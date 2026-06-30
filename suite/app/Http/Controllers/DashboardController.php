<?php

namespace App\Http\Controllers;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use App\Models\BillingQueue;
use App\Models\ModuleRequest;
use App\Models\PlatformInvoice;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Platform admin (no tenant) → owner analytics dashboard
        if (!$user->tenant_id) {
            return $this->ownerDashboard();
        }

        // Client portal users
        if ($user->isClient()) {
            return redirect()->route('portal.dashboard');
        }

        // No modules activated yet — send to module selection (onboarding step)
        $tenant = $user->tenant;
        $tenant->load('activeModules');
        if ($tenant->activeModules->isEmpty()) {
            return redirect()->route('settings.modules.index')
                ->with('info', 'Welcome! Choose the modules you want to activate for your business.');
        }

        $hasPos  = $user->tenant?->hasModule('pos') ?? false;
        $tenantId = $user->tenant_id;

        // Cache aggregate counts per tenant — 60 seconds
        $stats = Cache::remember("tenant-dashboard-stats:{$tenantId}", 60, function () use ($hasPos) {
            $completedThisMonth = Appointment::with(['services', 'sale'])
                ->where('status', 'completed')
                ->whereMonth('scheduled_at', now()->month)
                ->whereYear('scheduled_at', now()->year)
                ->get();

            $awaitingAppts = Appointment::with('services')
                ->where('status', 'awaiting_payment')
                ->get();

            return [
                'todayCount'            => Appointment::today()->count(),
                'totalCustomers'        => Customer::count(),
                'activeStaff'           => Staff::where('is_active', true)->count(),
                'activeServices'        => Service::where('is_active', true)->count(),
                'reorderCount'          => $hasPos ? Product::where('track_stock', true)
                    ->where('reorder_level', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'reorder_level')
                    ->count() : 0,
                'pendingModuleRequests' => ModuleRequest::pending()->count(),
                'completedRevenue'      => $completedThisMonth->sum(function ($appt) {
                    if ($appt->sale) return (float) $appt->sale->total;
                    return $appt->services->sum(fn($s) => (float)($s->pivot->price_at_booking ?? $s->price));
                }),
                'completedCount'        => $completedThisMonth->count(),
                'awaitingTotal'         => $awaitingAppts->sum(fn($appt) =>
                    $appt->services->sum(fn($s) => (float)($s->pivot->price_at_booking ?? $s->price))
                ),
                'awaitingCount'         => $awaitingAppts->count(),
            ];
        });

        [
            'todayCount'            => $todayCount,
            'totalCustomers'        => $totalCustomers,
            'activeStaff'           => $activeStaff,
            'activeServices'        => $activeServices,
            'reorderCount'          => $reorderCount,
            'pendingModuleRequests' => $pendingModuleRequests,
            'completedRevenue'      => $completedRevenue,
            'completedCount'        => $completedCount,
            'awaitingTotal'         => $awaitingTotal,
            'awaitingCount'         => $awaitingCount,
        ] = $stats;

        $recentAppointments = Appointment::with(['customer', 'staff', 'services'])
            ->orderByDesc('scheduled_at')
            ->limit(8)
            ->get();

        $upcomingToday = Appointment::with(['customer', 'staff', 'services'])
            ->today()
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->get();

        // Onboarding checklist — shown until all steps are complete
        $onboarding = [
            'has_service'     => Service::where('is_active', true)->exists(),
            'has_staff'       => Staff::where('is_active', true)->exists(),
            'has_appointment' => Appointment::exists(),
            'has_product'     => $hasPos ? Product::where('is_active', true)->exists() : true,
        ];
        $onboardingComplete = collect($onboarding)->every(fn($v) => $v === true);

        // Profile completeness — fields clients and the system need
        $profileChecks = [
            ['key' => 'phone',    'label' => 'Business phone number',   'filled' => !empty($tenant->phone)],
            ['key' => 'logo',     'label' => 'Business logo',            'filled' => !empty($tenant->logo_url)],
            ['key' => 'address',  'label' => 'Business address',         'filled' => !empty($tenant->address)],
            ['key' => 'banking',  'label' => 'Banking details',          'filled' => !empty($tenant->bank_name) && !empty($tenant->bank_account_number)],
        ];
        $profileComplete = collect($profileChecks)->every(fn($c) => $c['filled']);

        return view('dashboard', compact(
            'hasPos',
            'todayCount',
            'totalCustomers',
            'activeStaff',
            'activeServices',
            'reorderCount',
            'pendingModuleRequests',
            'recentAppointments',
            'upcomingToday',
            'onboarding',
            'onboardingComplete',
            'completedRevenue',
            'completedCount',
            'awaitingTotal',
            'awaitingCount',
            'profileChecks',
            'profileComplete',
        ));
    }

    private function ownerDashboard()
    {
        $now          = now();
        $thisMonth    = $now->copy()->startOfMonth();
        $lastMonth    = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Cache heavy aggregate stats for 2 minutes — these don't need to be real-time
        $ownerStats = Cache::remember('owner-dashboard-stats', 120, function () use ($now, $thisMonth, $lastMonth, $lastMonthEnd) {
            $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();

            $totalTenants     = Tenant::count();
            $activeTenants    = Tenant::where('is_active', true)->whereNull('suspended_at')->count();
            $trialTenants     = Tenant::whereNotNull('trial_ends_at')->where('trial_ends_at', '>', $now)->whereNull('suspended_at')->count();
            $paidTenants      = Tenant::where('is_active', true)->whereNull('suspended_at')
                                    ->where(fn($q) => $q->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', $now))
                                    ->count();
            $suspendedTenants = Tenant::whereNotNull('suspended_at')->count();
            $graceTenants     = Tenant::whereNotNull('grace_period_ends_at')->whereNull('suspended_at')->count();
            $newThisMonth     = Tenant::where('created_at', '>=', $thisMonth)->count();
            $newLastMonth     = Tenant::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

            $mrr        = Tenant::where('is_active', true)->whereNull('suspended_at')->get()->sum(fn($t) => $t->monthlyTotal());
            $avgMrr     = $paidTenants > 0 ? round($mrr / max($paidTenants, 1), 2) : 0;

            $collectedThisMonth = PlatformInvoice::where('status', 'paid')->where('paid_at', '>=', $thisMonth)->sum('amount');
            $collectedLastMonth = PlatformInvoice::where('status', 'paid')->whereBetween('paid_at', [$lastMonth, $lastMonthEnd])->sum('amount');
            $invoicedThisMonth  = PlatformInvoice::where('created_at', '>=', $thisMonth)->sum('amount');
            $outstandingTotal   = PlatformInvoice::whereIn('status', ['unpaid', 'overdue'])->sum('amount');
            $overdueTotal       = PlatformInvoice::where('status', 'overdue')->sum('amount');
            $overdueCount       = PlatformInvoice::where('status', 'overdue')->count();
            $popPending         = PlatformInvoice::whereNotNull('pop_path')->whereIn('status', ['unpaid', 'overdue'])->count();
            $collectionRate     = $invoicedThisMonth > 0 ? round(($collectedThisMonth / $invoicedThisMonth) * 100) : 0;
            $revenueGrowth      = $collectedLastMonth > 0
                ? round((($collectedThisMonth - $collectedLastMonth) / $collectedLastMonth) * 100, 1) : null;

            $monthlyRevenue = PlatformInvoice::where('status', 'paid')
                ->where('paid_at', '>=', $sixMonthsAgo)
                ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total")
                ->groupBy('month')->orderBy('month')->pluck('total', 'month');

            $monthlySignups = Tenant::where('created_at', '>=', $sixMonthsAgo)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->groupBy('month')->orderBy('month')->pluck('count', 'month');

            $months = collect();
            for ($i = 5; $i >= 0; $i--) {
                $months->push($now->copy()->subMonths($i)->format('Y-m'));
            }
            $revenueByMonth = $months->mapWithKeys(fn($m) => [$m => (float)($monthlyRevenue[$m] ?? 0)]);
            $signupsByMonth = $months->mapWithKeys(fn($m) => [$m => (int)($monthlySignups[$m] ?? 0)]);

            $moduleStats = TenantModule::where('is_active', true)
                ->select('module', DB::raw('count(*) as tenant_count'))
                ->groupBy('module')->orderByDesc('tenant_count')
                ->with('platformModule')->get();

            return compact(
                'totalTenants', 'activeTenants', 'trialTenants', 'paidTenants',
                'suspendedTenants', 'graceTenants', 'newThisMonth', 'newLastMonth',
                'mrr', 'avgMrr', 'collectedThisMonth', 'collectedLastMonth',
                'invoicedThisMonth', 'collectionRate', 'revenueGrowth',
                'outstandingTotal', 'overdueTotal', 'overdueCount', 'popPending',
                'revenueByMonth', 'signupsByMonth', 'months', 'moduleStats'
            );
        });

        extract($ownerStats);

        // ── Ops (live — these are action items) ───────────────────
        $billingQueueCount = BillingQueue::pendingCount();
        $pendingModReqs    = ModuleRequest::where('status', 'pending')->count();
        $totalUsers        = User::whereNotNull('tenant_id')->where('is_active', true)->count();

        // ── Attention list ────────────────────────────────────────
        $attentionTenants = Tenant::with('platformInvoices')
            ->where(fn($q) => $q->whereNotNull('suspended_at')
                ->orWhereNotNull('grace_period_ends_at')
                ->orWhereHas('platformInvoices', fn($q2) => $q2->where('status', 'overdue')))
            ->orderByDesc('suspended_at')
            ->limit(8)->get();

        // ── Recent sign-ups ───────────────────────────────────────
        $recentTenants = Tenant::with('tenantModules')->latest()->limit(8)->get();

        return view('dashboard-owner', compact(
            'totalTenants', 'activeTenants', 'trialTenants', 'paidTenants',
            'suspendedTenants', 'graceTenants', 'newThisMonth', 'newLastMonth',
            'mrr', 'avgMrr', 'collectedThisMonth', 'collectedLastMonth',
            'invoicedThisMonth', 'collectionRate', 'revenueGrowth',
            'outstandingTotal', 'overdueTotal', 'overdueCount', 'popPending',
            'revenueByMonth', 'signupsByMonth', 'months',
            'moduleStats', 'billingQueueCount', 'pendingModReqs', 'totalUsers',
            'attentionTenants', 'recentTenants',
        ));
    }
}
