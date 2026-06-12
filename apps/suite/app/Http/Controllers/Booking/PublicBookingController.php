<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Service;
use App\Models\Tenant;
use App\Services\Booking\AvailabilityService;
use App\Services\Notifications\BookingNotificationService;
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
        $services = Service::where('is_active', true)
            ->whereHas('staff', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $servicesJson = $services->map(fn($s) => [
            'id'               => $s->id,
            'duration_minutes' => $s->duration_minutes,
            'price'            => (float) $s->price,
        ])->values();

        return view('booking.index', compact('tenant', 'slug', 'services', 'servicesJson'));
    }

    /** Step 2 — pick a date + time for selected services */
    public function service(string $slug, Request $request)
    {
        $tenant   = $this->resolveTenant($slug);
        $services = Service::findMany($request->input('service_ids', []));

        if ($services->isEmpty()) {
            return redirect()->route('book.index', $slug)
                ->withErrors(['service_ids' => 'Please select at least one service.']);
        }

        return view('booking.service', compact('tenant', 'slug', 'services'));
    }

    /** AJAX — return available slots for selected services + date */
    public function slots(string $slug, Request $request, AvailabilityService $availability)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'date'          => 'required|date|after_or_equal:today',
        ]);

        // Sum duration across all selected services
        $services      = Service::findMany($request->input('service_ids'));
        $totalDuration = $services->sum('duration_minutes');
        $date          = Carbon::parse($request->date);

        $slots = $availability->availableSlotsForDuration($totalDuration, $date);

        return response()->json([
            'slots' => $slots->map(fn($s) => [
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
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'scheduled_at'  => 'required|date|after:now',
        ]);

        $services = Service::findMany($request->input('service_ids'));
        $slot     = Carbon::parse($request->scheduled_at);

        session(['pending_booking' => [
            'service_ids'  => $request->input('service_ids'),
            'scheduled_at' => $request->scheduled_at,
        ]]);

        $customer = auth('customer')->user();

        return view('booking.confirm', compact('tenant', 'services', 'slot', 'slug', 'customer'));
    }

    /** Step 4 — create the appointment */
    public function store(string $slug, Request $request, AvailabilityService $availability, BookingNotificationService $notifications)
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

        $services      = Service::findMany($pending['service_ids']);
        $totalDuration = $services->sum('duration_minutes');
        $start         = Carbon::parse($pending['scheduled_at']);

        // Verify the combined slot is still available
        $slots          = $availability->availableSlotsForDuration($totalDuration, $start->copy()->startOfDay());
        $stillAvailable = $slots->contains(fn($s) => $s->format('Y-m-d H:i') === $start->format('Y-m-d H:i'));

        if (!$stillAvailable) {
            session()->forget('pending_booking');
            return redirect()->route('book.service', $slug)
                ->with('service_ids', $pending['service_ids'])
                ->withErrors(['slot' => 'That time slot is no longer available. Please choose another.']);
        }

        $notes = $request->validate(['notes' => 'nullable|string|max:1000'])['notes'] ?? null;

        $appointment = Appointment::create([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'staff_id'         => null,
            'scheduled_at'     => $start,
            'duration_minutes' => $totalDuration,
            'status'           => 'pending',
            'notes'            => $notes,
        ]);

        // Attach all services with price snapshots
        $appointment->services()->sync(
            $services->mapWithKeys(fn($service, $index) => [
                $service->id => [
                    'duration_minutes' => $service->duration_minutes,
                    'price_at_booking' => $service->price,
                    'sort_order'       => $index,
                ],
            ])->all()
        );

        $notifications->notifyAppointmentCreated($appointment, route('book.success', [$slug, $appointment]));

        session()->forget('pending_booking');

        return redirect()->route('book.success', [$slug, $appointment]);
    }

    /** Confirmation page */
    public function success(string $slug, Appointment $appointment)
    {
        $tenant = $this->resolveTenant($slug);
        $appointment->load(['customer', 'services']);

        abort_if(auth('customer')->id() !== $appointment->customer_id, 403);

        return view('booking.success', compact('tenant', 'appointment', 'slug'));
    }

    /** Customer reschedule */
    public function updateBooking(string $slug, Appointment $appointment, Request $request, AvailabilityService $availability)
    {
        $this->resolveTenant($slug);

        abort_if(auth('customer')->id() !== $appointment->customer_id, 403);
        abort_if(!in_array($appointment->status, ['pending', 'confirmed']), 422, 'This appointment cannot be modified.');

        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $start         = Carbon::parse($request->scheduled_at);
        $totalDuration = $appointment->services->sum('duration_minutes');

        $slots          = $availability->availableSlotsForDuration($totalDuration, $start->copy()->startOfDay());
        $stillAvailable = $slots->contains(fn($s) => $s->format('Y-m-d H:i') === $start->format('Y-m-d H:i'));

        if (!$stillAvailable) {
            return back()->withErrors(['scheduled_at' => 'That time slot is no longer available. Please choose another.']);
        }

        $appointment->update(['scheduled_at' => $start]);

        return redirect()->route('book.my-bookings', $slug)
            ->with('success', 'Your appointment has been updated.');
    }

    /** Customer edit page */
    public function edit(string $slug, Appointment $appointment)
    {
        $tenant = $this->resolveTenant($slug);

        abort_if(auth('customer')->id() !== $appointment->customer_id, 403);
        abort_if(!in_array($appointment->status, ['pending', 'confirmed']), 422, 'This appointment cannot be modified.');

        $services = Service::where('is_active', true)
            ->whereHas('staff', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        return view('booking.edit', compact('tenant', 'appointment', 'slug', 'services'));
    }
}