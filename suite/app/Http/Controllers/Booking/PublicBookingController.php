<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentConfirmationEmail;
use App\Models\Promotion;
use App\Models\ServiceCombo;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Service;
use App\Models\Tenant;
use App\Services\Booking\AvailabilityService;
use App\Services\Notifications\BookingNotificationService;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PublicBookingController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    /** Step 1 — list services, combos, and promotions */
    public function index(string $slug)
    {
        $tenant   = $this->resolveTenant($slug);
        $services = Service::where('is_active', true)
            ->whereHas('staff', fn($q) => $q->where('is_active', true))
            ->with('category')
            ->orderBy('created_at')
            ->get();

        $bookableIds  = $services->pluck('id');

        $servicesJson = $services->map(fn($s) => [
            'id'               => $s->id,
            'name'             => $s->name,
            'duration_minutes' => (int) $s->duration_minutes,
            'price'            => (float) $s->price,
        ])->values();

        // Only combos whose every service is bookable right now
        $combos = ServiceCombo::where('is_active', true)
            ->with('services')
            ->get()
            ->filter(fn($c) =>
                $c->isLive() &&
                $c->services->isNotEmpty() &&
                $c->services->pluck('id')->diff($bookableIds)->isEmpty()
            )
            ->values();

        $promotions = Promotion::where('is_active', true)
            ->get()
            ->filter(fn($p) => $p->isLive())
            ->values();

        $combosJson = $combos->map(fn($c) => [
            'id'         => $c->id,
            'name'       => $c->name,
            'price'      => (float) $c->combo_price,
            'savings'    => (float) $c->savings,
            'serviceIds' => $c->services->pluck('id')->values()->all(),
        ])->values();

        return view('booking.index', compact('tenant', 'slug', 'services', 'servicesJson', 'combos', 'combosJson', 'promotions'));
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

        $combo = $this->detectCombo($request->input('combo_id'), $services->pluck('id')->all());

        return view('booking.service', compact('tenant', 'slug', 'services', 'combo'));
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
        $serviceIds    = $request->input('service_ids');
        $services      = Service::findMany($serviceIds);
        $totalDuration = $services->sum('duration_minutes');
        $date          = Carbon::parse($request->date);

        $slots = $availability->availableSlotsForDuration($totalDuration, $date, $serviceIds);

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

        $combo = $this->detectCombo($request->input('combo_id'), $services->pluck('id')->all());

        session(['pending_booking' => [
            'service_ids'  => $request->input('service_ids'),
            'scheduled_at' => $request->scheduled_at,
            'combo_id'     => $combo?->id,
        ]]);

        $customer = auth('customer')->user();

        return view('booking.confirm', compact('tenant', 'services', 'slot', 'slug', 'customer', 'combo'));
    }

    /** AJAX — validate a promo code against the pending booking session */
    public function checkPromo(string $slug, Request $request)
    {
        $this->resolveTenant($slug);
        $pending = session('pending_booking');

        if (!$pending) {
            return response()->json(['valid' => false, 'message' => 'Session expired. Please start your booking again.']);
        }

        // Promos cannot stack on combo deals
        if (!empty($pending['combo_id'])) {
            return response()->json(['valid' => false, 'message' => 'Promo codes cannot be combined with combo deals.']);
        }

        $code  = strtoupper(trim($request->input('code', '')));
        $promo = Promotion::where('code', $code)->where('is_active', true)->first();

        if (!$promo || !$promo->isLive() || !in_array($promo->applies_to, ['all', 'services'])) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired promo code.']);
        }

        $services = Service::findMany($pending['service_ids'] ?? []);
        $total    = (float) $services->sum('price');
        $discount = $promo->discount_type === 'percentage'
            ? round($total * $promo->discount_value / 100, 2)
            : min((float) $promo->discount_value, $total);

        return response()->json([
            'valid'     => true,
            'discount'  => number_format($discount, 2),
            'new_total' => number_format(max(0, $total - $discount), 2),
            'label'     => $promo->discount_type === 'percentage'
                ? $promo->discount_value . '% off'
                : 'R' . number_format($promo->discount_value, 2) . ' off',
        ]);
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
        $serviceIds    = $services->pluck('id')->all();
        $totalDuration = $services->sum('duration_minutes');
        $start         = Carbon::parse($pending['scheduled_at']);

        // Verify the combined slot is still available
        $slots          = $availability->availableSlotsForDuration($totalDuration, $start->copy()->startOfDay(), $serviceIds);
        $stillAvailable = $slots->contains(fn($s) => $s->format('Y-m-d H:i') === $start->format('Y-m-d H:i'));

        if (!$stillAvailable) {
            session()->forget('pending_booking');
            return redirect()->route('book.service', $slug)
                ->with('service_ids', $pending['service_ids'])
                ->withErrors(['slot' => 'That time slot is no longer available. Please choose another.']);
        }

        $notes = $request->validate(['notes' => 'nullable|string|max:1000'])['notes'] ?? null;

        // Auto-detect combo pricing (works even if combo_id wasn't threaded through session)
        $combo      = $this->detectCombo($pending['combo_id'] ?? null, $serviceIds);
        $comboId    = $combo?->id;
        $comboPrice = $combo?->combo_price;

        // Promo + appointment creation inside a transaction so lockForUpdate() is effective
        $promoCode     = null;
        $promoDiscount = null;

        $appointment = DB::transaction(function () use (
            $combo, $request, $services, $tenant, $customer, $start,
            $totalDuration, $notes, $comboId, $comboPrice, &$promoCode, &$promoDiscount
        ) {
            if (!$combo && $request->filled('promo_code')) {
                $code = strtoupper(trim($request->input('promo_code')));

                // Lock the row so concurrent requests can't over-redeem max_uses
                $promo = Promotion::where('code', $code)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if ($promo && $promo->isLive() && in_array($promo->applies_to, ['all', 'services'])) {
                    $baseTotal     = (float) $services->sum('price');
                    $promoDiscount = $promo->discount_type === 'percentage'
                        ? round($baseTotal * $promo->discount_value / 100, 2)
                        : min((float) $promo->discount_value, $baseTotal);
                    $promoCode = $promo->code;
                    $promo->increment('used_count');
                } else {
                    // Signal caller to redirect back with error
                    return null;
                }
            }

            $appt = Appointment::create([
                'tenant_id'        => $tenant->id,
                'customer_id'      => $customer->id,
                'staff_id'         => null,
                'scheduled_at'     => $start,
                'duration_minutes' => $totalDuration,
                'status'           => 'pending',
                'notes'            => $notes,
                'combo_id'         => $comboId,
                'combo_price'      => $comboPrice,
                'promo_code'       => $promoCode,
                'promo_discount'   => $promoDiscount,
            ]);

            $appt->services()->sync(
                $services->mapWithKeys(fn($service, $index) => [
                    $service->id => [
                        'duration_minutes' => $service->duration_minutes,
                        'price_at_booking' => $service->price,
                        'sort_order'       => $index,
                    ],
                ])->all()
            );

            return $appt;
        });

        if ($appointment === null) {
            return back()->withErrors(['promo_code' => 'Invalid or expired promo code.'])->withInput();
        }

        $notifications->notifyAppointmentCreated($appointment, route('book.success', [$slug, $appointment]));

        // Confirmation email to the customer
        $appointment->load(['customer', 'services']);
        if ($customer->email) {
            Mail::to($customer->email)
                ->queue(new AppointmentConfirmationEmail($appointment, recipient: 'customer'));
        }

        // Notify the tenant owner so they see the new self-booking
        $tenantOwnerEmail = $tenant->owner()?->email;
        if ($tenantOwnerEmail) {
            Mail::to($tenantOwnerEmail)
                ->queue(new AppointmentConfirmationEmail($appointment, recipient: 'booker'));
        }

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
        $serviceIds    = $appointment->services->pluck('id')->all();

        $slots          = $availability->availableSlotsForDuration($totalDuration, $start->copy()->startOfDay(), $serviceIds);
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

        $appointment->load('services');

        $services = Service::where('is_active', true)
            ->whereHas('staff', fn($q) => $q->where('is_active', true))
            ->orderBy('created_at')
            ->get();

        return view('booking.edit', compact('tenant', 'appointment', 'slug', 'services'));
    }

    /**
     * Find a live combo whose services are a subset of (or exactly match) the selected service IDs.
     * When multiple combos match, picks the one with the highest savings.
     * Falls back to auto-detection when combo_id isn't explicitly passed.
     */
    private function detectCombo(?string $comboId, array $serviceIds): ?ServiceCombo
    {
        if ($comboId) {
            $combo = ServiceCombo::with('services')->find($comboId);
            if ($combo && $combo->isLive()) {
                $comboServiceIds = $combo->services->pluck('id')->all();
                // Combo is valid as long as all its services are present in the selection
                if (collect($comboServiceIds)->every(fn($id) => in_array($id, $serviceIds))) {
                    return $combo;
                }
            }
        }

        if (count($serviceIds) < 2) {
            return null;
        }

        return ServiceCombo::where('is_active', true)
            ->with('services')
            ->get()
            ->filter(function ($c) use ($serviceIds) {
                if (!$c->isLive() || $c->services->isEmpty()) {
                    return false;
                }
                // Every combo service must appear in the selected services
                return $c->services->every(fn($s) => in_array($s->id, $serviceIds));
            })
            ->sortByDesc(fn($c) => $c->savings)
            ->first();
    }
}