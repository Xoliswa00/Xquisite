<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\Staff;
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

    /** Step 2 — pick a date + staff for a service */
    public function service(string $slug, Service $service)
    {
        $tenant = $this->resolveTenant($slug);
        $staff  = Staff::where('is_active', true)
            ->whereHas('services', fn ($q) => $q->where('services.id', $service->id))
            ->orderBy('name')
            ->get();

        return view('booking.service', compact('tenant', 'service', 'staff', 'slug'));
    }

    /** AJAX — return available slots for a staff + date + service */
    public function slots(string $slug, Request $request, AvailabilityService $availability)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'staff_id'   => 'required|exists:staff,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date|after_or_equal:today',
        ]);

        $staff   = Staff::findOrFail($request->staff_id);
        $service = Service::findOrFail($request->service_id);
        $date    = Carbon::parse($request->date);

        $slots = $availability->availableSlots($staff, $date, $service->duration_minutes);

        return response()->json([
            'slots' => $slots->map(fn ($s) => [
                'value' => $s->format('Y-m-d H:i'),
                'label' => $s->format('H:i'),
            ]),
        ]);
    }

    /** Step 3 — review & confirm (shown after slot selection) */
    public function confirm(string $slug, Request $request)
    {
        $tenant  = $this->resolveTenant($slug);
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'staff_id'     => 'required|exists:staff,id',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $service = Service::findOrFail($request->service_id);
        $staff   = Staff::findOrFail($request->staff_id);
        $slot    = Carbon::parse($request->scheduled_at);

        // Store pending booking in session so confirm POST can use it
        session(['pending_booking' => $request->only('service_id', 'staff_id', 'scheduled_at')]);

        $customer = auth('customer')->user();

        return view('booking.confirm', compact('tenant', 'service', 'staff', 'slot', 'slug', 'customer'));
    }

    /** Step 4 — create the appointment */
    public function store(string $slug, Request $request, AvailabilityService $availability)
    {
        $tenant = $this->resolveTenant($slug);

        $pending = session('pending_booking');
        if (!$pending) {
            return redirect()->route('book.index', $slug)->withErrors(['error' => 'Session expired. Please start again.']);
        }

        $customer = auth('customer')->user();
        if (!$customer) {
            return redirect()->route('book.login', $slug)->with('info', 'Please log in or create an account to complete your booking.');
        }

        $service  = Service::findOrFail($pending['service_id']);
        $staff    = Staff::findOrFail($pending['staff_id']);
        $start    = Carbon::parse($pending['scheduled_at']);

        $error = $availability->check($staff, $start, $service->duration_minutes);
        if ($error) {
            session()->forget('pending_booking');
            return redirect()->route('book.service', [$slug, $service])->withErrors(['slot' => $error]);
        }

        $appointment = Appointment::create([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'staff_id'         => $staff->id,
            'service_id'       => $service->id,
            'scheduled_at'     => $start,
            'duration_minutes' => $service->duration_minutes,
            'status'           => 'confirmed',
            'notes'            => $request->input('notes'),
        ]);

        session()->forget('pending_booking');

        return redirect()->route('book.success', [$slug, $appointment]);
    }

    /** Confirmation page */
    public function success(string $slug, Appointment $appointment)
    {
        $tenant = $this->resolveTenant($slug);
        $appointment->load(['customer', 'staff', 'service']);

        // Ensure this customer owns this appointment
        abort_if(auth('customer')->id() !== $appointment->customer_id, 403);

        return view('booking.success', compact('tenant', 'appointment', 'slug'));
    }
}
