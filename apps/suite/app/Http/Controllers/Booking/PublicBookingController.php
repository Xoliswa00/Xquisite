<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Service;
use App\Models\Tenant;
use App\Services\Booking\AvailabilityService;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicBookingController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    /** Step 1 — list services */
    public function index(string $slug)
    {
        $tenant   = $this->resolveTenant($slug);
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('booking.index', compact('tenant', 'services', 'slug'));
    }

    /** Step 2 — pick a date + time for a service (no staff selection) */
    public function service(string $slug, Service $service)
    {
        $tenant = $this->resolveTenant($slug);

        return view('booking.service', compact('tenant', 'service', 'slug'));
    }

    /** AJAX — return available slots for a service + date (checks all staff) */
    public function slots(string $slug, Request $request, AvailabilityService $availability)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date|after_or_equal:today',
        ]);

        $service = Service::findOrFail($request->service_id);
        $date    = Carbon::parse($request->date);

        $slots = $availability->availableSlotsForService($service, $date);

        return response()->json([
            'slots' => $slots->map(fn ($s) => [
                'value' => $s->format('Y-m-d H:i'),
                'label' => $s->format('H:i'),
            ]),
        ]);
    }

    /** Step 3 — review & confirm */
    public function confirm(string $slug, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $service = Service::findOrFail($request->service_id);
        $slot    = Carbon::parse($request->scheduled_at);

        session(['pending_booking' => $request->only('service_id', 'scheduled_at')]);

        $customer = auth('customer')->user();

        return view('booking.confirm', compact('tenant', 'service', 'slot', 'slug', 'customer'));
    }

    /** Step 4 — create the appointment (staff unassigned — admin assigns later) */
    public function store(string $slug, Request $request, AvailabilityService $availability)
    {
        $tenant  = $this->resolveTenant($slug);
        $pending = session('pending_booking');

        if (!$pending) {
            return redirect()->route('book.index', $slug)
                ->withErrors(['error' => 'Session expired. Please start again.']);
        }

        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('book.login', $slug)
                ->with('info', 'Please log in or create an account to complete your booking.');
        }

        $service = Service::findOrFail($pending['service_id']);
        $start   = Carbon::parse($pending['scheduled_at']);

        // Verify at least one staff member is still available at this slot
        $slots = $availability->availableSlotsForService($service, $start->copy()->startOfDay());
        $stillAvailable = $slots->contains(fn ($s) => $s->format('Y-m-d H:i') === $start->format('Y-m-d H:i'));

        if (!$stillAvailable) {
            session()->forget('pending_booking');
            return redirect()->route('book.service', [$slug, $service])
                ->withErrors(['slot' => 'That time slot is no longer available. Please choose another.']);
        }

        $appointment = Appointment::create([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'staff_id'         => null,   // assigned by admin at confirmation
            'service_id'       => $service->id,
            'scheduled_at'     => $start,
            'duration_minutes' => $service->duration_minutes,
            'status'           => 'pending',
            'notes'            => $request->input('notes'),
        ]);

        session()->forget('pending_booking');

        return redirect()->route('book.success', [$slug, $appointment]);
    }

    /** Confirmation page */
    public function success(string $slug, Appointment $appointment)
    {
        $tenant = $this->resolveTenant($slug);
        $appointment->load(['customer', 'service']);

        abort_if(auth('customer')->id() !== $appointment->customer_id, 403);

        return view('booking.success', compact('tenant', 'appointment', 'slug'));
    }
}
