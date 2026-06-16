<?php

namespace App\Http\Controllers;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use App\Models\ModuleRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Platform admin (no tenant) → admin panel
        if (!$user->tenant_id) {
            return redirect()->route('admin.tenants.index');
        }

        // Client portal users
        if ($user->isClient()) {
            return redirect()->route('portal.dashboard');
        }

        $hasPos         = $user->tenant?->hasModule('pos') ?? false;
        $todayCount     = Appointment::today()->count();
        $totalCustomers = Customer::count();
        $activeStaff    = Staff::where('is_active', true)->count();
        $activeServices = Service::where('is_active', true)->count();
        $reorderCount   = $hasPos ? Product::where('track_stock', true)
            ->where('reorder_level', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->count() : 0;

        $pendingModuleRequests = ModuleRequest::pending()->count();

        // Revenue tracking — completed this month
        $completedThisMonth = Appointment::with(['services', 'sale'])
            ->where('status', 'completed')
            ->whereMonth('scheduled_at', now()->month)
            ->whereYear('scheduled_at', now()->year)
            ->get();

        $completedRevenue = $completedThisMonth->sum(function ($appt) {
            if ($appt->sale) return (float) $appt->sale->total;
            return $appt->services->sum(fn($s) => (float)($s->pivot->price_at_booking ?? $s->price));
        });
        $completedCount = $completedThisMonth->count();

        // Outstanding — awaiting payment (all time, not yet paid)
        $awaitingAppts = Appointment::with('services')
            ->where('status', 'awaiting_payment')
            ->get();

        $awaitingTotal = $awaitingAppts->sum(fn($appt) =>
            $appt->services->sum(fn($s) => (float)($s->pivot->price_at_booking ?? $s->price))
        );
        $awaitingCount = $awaitingAppts->count();

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
        ));
    }
}
