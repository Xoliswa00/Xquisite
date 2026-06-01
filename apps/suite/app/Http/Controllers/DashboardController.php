<?php

namespace App\Http\Controllers;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use App\Models\AuditLog;
use App\Models\ModuleRequest;
use App\Models\ReviewPrompt;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $todayCount     = Appointment::today()->count();
        $totalCustomers = Customer::count();
        $activeStaff    = Staff::where('is_active', true)->count();
        $activeServices = Service::where('is_active', true)->count();
        $reorderCount   = Product::where('track_stock', true)
            ->where('reorder_level', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->count();

        $pendingModuleRequests = ModuleRequest::pending()->count();

        $recentAppointments = Appointment::with(['customer', 'staff', 'service'])
            ->orderByDesc('scheduled_at')
            ->limit(8)
            ->get();

        $upcomingToday = Appointment::with(['customer', 'staff', 'service'])
            ->today()
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->get();

        // Onboarding checklist — shown until all steps are complete
        $onboarding = [
            'has_service'     => Service::where('is_active', true)->exists(),
            'has_staff'       => Staff::where('is_active', true)->exists(),
            'has_appointment' => Appointment::exists(),
            'has_product'     => Product::where('is_active', true)->exists(),
        ];
        $onboardingComplete = collect($onboarding)->every(fn($v) => $v === true);

        // ── Review milestone prompt ────────────────────────────────────────────
        $userId      = auth()->id();
        $auditCount  = Cache::remember("audit_count:{$userId}", 3600, fn () =>
            AuditLog::where('user_id', $userId)->count()
        );
        $reviewThreshold = ReviewPrompt::nextThresholdFor($userId, $auditCount);

        if ($reviewThreshold) {
            ReviewPrompt::firstOrCreate(
                ['user_id' => $userId, 'threshold' => $reviewThreshold],
                ['shown_at' => now()]
            );
        }

        return view('dashboard', compact(
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
            'reviewThreshold'
        ));
    }
}
