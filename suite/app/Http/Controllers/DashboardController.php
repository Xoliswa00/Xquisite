<?php

namespace App\Http\Controllers;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Unit;
use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\RentPayment;
use App\Modules\Property\Models\MaintenanceRequest;
use App\Modules\Property\Models\Renter;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\POS\Models\Sale;
use App\Models\BillingQueue;
use App\Models\Client;
use App\Models\Communication;
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

        $tenantId = $user->tenant_id;

        $hasBooking   = $tenant->hasModule('booking');
        $hasPos       = $tenant->hasModule('pos');
        $hasProperty  = $tenant->hasModule('property_management');
        $hasEcommerce = $tenant->hasModule('ecommerce');
        $hasMessaging = $tenant->hasModule('client_messaging');

        // Each module's dashboard section is only computed — and only shown — when that module is active.
        $data = [
            'hasBooking'   => $hasBooking,
            'hasPos'       => $hasPos,
            'hasProperty'  => $hasProperty,
            'hasEcommerce' => $hasEcommerce,
            'hasMessaging' => $hasMessaging,
            'pendingModuleRequests' => ModuleRequest::pending()->count(),
        ];

        $bookingData = $posData = $propertyData = $ecommerceData = $messagingData = [];

        if ($hasBooking) {
            $bookingData = $this->bookingStats($tenantId, $hasPos);
            $data += $bookingData;
        }
        if ($hasPos && !$hasBooking) {
            $posData = $this->posStats($tenantId);
            $data += $posData;
        }
        if ($hasProperty) {
            $propertyData = $this->propertyStats($tenantId);
            $data += $propertyData;
        }
        if ($hasEcommerce) {
            $ecommerceData = $this->ecommerceStats($tenantId);
            $data += $ecommerceData;
        }
        if ($hasMessaging) {
            $messagingData = $this->messagingStats($tenantId);
            $data += $messagingData;
        }

        // Onboarding checklist — built from whichever modules the tenant actually has active,
        // reusing stats already fetched above instead of re-querying the same counts.
        $onboardingSteps = $this->buildOnboardingSteps(
            $hasBooking, $hasPos, $hasProperty, $hasEcommerce, $hasMessaging,
            $bookingData, $posData, $propertyData, $ecommerceData, $messagingData
        );
        $data['onboardingSteps'] = $onboardingSteps;
        $data['onboardingComplete'] = collect($onboardingSteps)->every(fn ($step) => $step['done']);

        // Profile completeness — fields clients and the system need (module-agnostic)
        $profileChecks = [
            ['key' => 'phone',    'label' => 'Business phone number',   'filled' => !empty($tenant->phone)],
            ['key' => 'logo',     'label' => 'Business logo',            'filled' => !empty($tenant->logo_url)],
            ['key' => 'address',  'label' => 'Business address',         'filled' => !empty($tenant->address)],
            ['key' => 'banking',  'label' => 'Banking details',          'filled' => !empty($tenant->bank_name) && !empty($tenant->bank_account_number)],
        ];
        $data['profileChecks'] = $profileChecks;
        $data['profileComplete'] = collect($profileChecks)->every(fn ($c) => $c['filled']);

        return view('dashboard', $data);
    }

    private function reorderCount(): int
    {
        return Product::where('track_stock', true)
            ->where('reorder_level', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->count();
    }

    private function bookingStats(int $tenantId, bool $hasPos): array
    {
        // Cache key includes the POS flag: the closure branches on $hasPos, so a tenant toggling
        // POS on/off must not read back a stale reorderCount computed under the other state.
        $cacheKey = 'tenant-dashboard-stats:booking:' . ($hasPos ? 'pos' : 'nopos') . ":{$tenantId}";

        $stats = Cache::remember($cacheKey, 60, function () use ($hasPos) {
            $completedThisMonth = Appointment::with(['services', 'sale'])
                ->where('status', 'completed')
                ->whereMonth('scheduled_at', now()->month)
                ->whereYear('scheduled_at', now()->year)
                ->get();

            $awaitingAppts = Appointment::with('services')
                ->where('status', 'awaiting_payment')
                ->get();

            return [
                'todayCount'       => Appointment::today()->count(),
                'totalCustomers'   => Customer::count(),
                'activeStaff'      => Staff::where('is_active', true)->count(),
                'activeServices'   => Service::where('is_active', true)->count(),
                'reorderCount'     => $hasPos ? $this->reorderCount() : 0,
                'completedRevenue' => $completedThisMonth->sum(function ($appt) {
                    if ($appt->sale) return (float) $appt->sale->total;
                    return $appt->services->sum(fn ($s) => (float) ($s->pivot->price_at_booking ?? $s->calculatePrice($s->pivot->quantity ?? 1)));
                }),
                'completedCount' => $completedThisMonth->count(),
                'awaitingTotal'  => $awaitingAppts->sum(fn ($appt) =>
                    $appt->services->sum(fn ($s) => (float) ($s->pivot->price_at_booking ?? $s->calculatePrice($s->pivot->quantity ?? 1)))
                ),
                'awaitingCount' => $awaitingAppts->count(),
            ];
        });

        $stats['recentAppointments'] = Appointment::with(['customer', 'staff', 'services'])
            ->orderByDesc('scheduled_at')
            ->limit(8)
            ->get();

        $stats['upcomingToday'] = Appointment::with(['customer', 'staff', 'services'])
            ->today()
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->get();

        return $stats;
    }

    private function posStats(int $tenantId): array
    {
        return Cache::remember("tenant-dashboard-stats:pos:{$tenantId}", 60, function () {
            return [
                'activeProductsCount' => Product::where('is_active', true)->count(),
                'reorderCount'        => $this->reorderCount(),
                'posRevenueThisMonth' => (float) Sale::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('total'),
            ];
        });
    }

    private function propertyStats(int $tenantId): array
    {
        $stats = Cache::remember("tenant-dashboard-stats:property:{$tenantId}", 60, function () {
            $totalUnits    = Unit::count();
            $occupiedUnits = Unit::where('status', 'occupied')->count();
            $vacantUnits   = Unit::where('status', 'vacant')->count();

            $overdue = RentPayment::where(function ($q) {
                    $q->where('status', 'overdue')
                        ->orWhere(fn ($q2) => $q2->where('status', 'pending')->where('due_date', '<', now()));
                })
                ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(amount_due - amount_paid), 0) as total')
                ->first();

            return [
                'totalUnits'              => $totalUnits,
                'occupiedUnits'           => $occupiedUnits,
                'vacantUnits'             => $vacantUnits,
                'occupancyRate'           => $totalUnits > 0 ? (int) round(($occupiedUnits / $totalUnits) * 100) : 0,
                'activeLeaseCount'        => Lease::where('status', 'active')->count(),
                'leasesExpiringSoonCount' => Lease::where('status', 'active')
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
                    ->count(),
                'rentCollectedThisMonth' => (float) RentPayment::where('period', now()->format('Y-m'))->sum('amount_paid'),
                'rentOverdueCount'       => (int) $overdue->cnt,
                'rentOverdueTotal'       => (float) $overdue->total,
                'pendingMaintenanceCount' => MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count(),
                'totalProperties'        => Property::count(),
            ];
        });

        $stats['leasesExpiringSoon'] = $stats['leasesExpiringSoonCount'] > 0
            ? Lease::with(['unit', 'renter'])
                ->where('status', 'active')
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
                ->orderBy('end_date')
                ->limit(5)
                ->get()
            : collect();

        return $stats;
    }

    private function ecommerceStats(int $tenantId): array
    {
        $stats = Cache::remember("tenant-dashboard-stats:ecommerce:{$tenantId}", 60, function () {
            return [
                'pendingOrdersCount' => Order::where('status', Order::STATUS_PENDING)->count(),
                'ecommerceRevenueThisMonth' => (float) Order::where('payment_status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('total'),
            ];
        });

        $stats['recentOrders'] = Order::orderByDesc('created_at')->limit(6)->get();

        return $stats;
    }

    private function messagingStats(int $tenantId): array
    {
        $stats = Cache::remember("tenant-dashboard-stats:messaging:{$tenantId}", 60, function () {
            return [
                'unreadMessagesCount' => Communication::whereNotNull('client_id')
                    ->where('is_from_owner', false)
                    ->whereNull('read_at')
                    ->count(),
                'totalClients' => Client::count(),
            ];
        });

        $stats['recentCommunications'] = Communication::whereNotNull('client_id')
            ->with('client')
            ->latest()
            ->limit(6)
            ->get();

        return $stats;
    }

    private function buildOnboardingSteps(
        bool $hasBooking, bool $hasPos, bool $hasProperty, bool $hasEcommerce, bool $hasMessaging,
        array $bookingData, array $posData, array $propertyData, array $ecommerceData, array $messagingData
    ): array {
        $steps = [];

        if ($hasBooking) {
            $steps[] = ['key' => 'has_service', 'label' => 'Add your first service', 'route' => 'services.create', 'done' => ($bookingData['activeServices'] ?? 0) > 0];
            $steps[] = ['key' => 'has_staff', 'label' => 'Add a staff member', 'route' => 'staff.create', 'done' => ($bookingData['activeStaff'] ?? 0) > 0];
            $steps[] = ['key' => 'has_appointment', 'label' => 'Create your first booking', 'route' => 'appointments.create', 'done' => ($bookingData['recentAppointments'] ?? collect())->isNotEmpty()];
        }

        if ($hasPos) {
            // When Booking is also active, POS's own product count isn't fetched separately (see posStats() guard) — check directly.
            $hasActiveProduct = array_key_exists('activeProductsCount', $posData)
                ? $posData['activeProductsCount'] > 0
                : Product::where('is_active', true)->exists();
            $steps[] = ['key' => 'has_product', 'label' => 'Add a product to stock', 'route' => 'products.create', 'done' => $hasActiveProduct];
        }

        if ($hasProperty) {
            // Ordered to match what leases.create actually requires (property_id, unit_id, renter_id all mandatory) —
            // sending a tenant to "create a lease" before they have a unit or renter dead-ends on an empty form.
            $steps[] = ['key' => 'has_property', 'label' => 'Add your first property', 'route' => 'properties.create', 'done' => ($propertyData['totalProperties'] ?? 0) > 0];
            $steps[] = ['key' => 'has_unit', 'label' => 'Add a unit to your property', 'route' => 'properties.index', 'done' => ($propertyData['totalUnits'] ?? 0) > 0];
            $steps[] = ['key' => 'has_renter', 'label' => 'Add a renter', 'route' => 'renters.create', 'done' => Renter::exists()];
            $steps[] = ['key' => 'has_lease', 'label' => 'Create your first lease', 'route' => 'leases.create', 'done' => Lease::exists()];
        }

        if ($hasEcommerce) {
            $steps[] = ['key' => 'has_order', 'label' => 'Get your first store order', 'route' => null, 'done' => ($ecommerceData['recentOrders'] ?? collect())->isNotEmpty()];
        }

        if ($hasMessaging) {
            $steps[] = ['key' => 'has_message', 'label' => 'Send your first client message', 'route' => 'clients.index', 'done' => ($messagingData['recentCommunications'] ?? collect())->isNotEmpty()];
        }

        return $steps;
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
