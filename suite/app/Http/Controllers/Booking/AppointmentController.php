<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentConfirmationEmail;
use App\Models\ServiceCombo;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use App\Notifications\AppointmentBookedNotification;
use App\Notifications\PaymentReminderNotification;
use App\Services\Booking\AvailabilityService;
use App\Services\Notifications\BookingNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['customer', 'staff', 'services'])  // service → services
            ->orderByDesc('scheduled_at');

        if ($request->filled('status')) {
            if ($request->status === 'unassigned') {
                $query->whereNull('staff_id');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        if ($request->filled('search')) {
            $search = trim(preg_replace('/\s+/', '%', $request->search));
            $query->whereHas('customer', fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            );
        }

        $appointments = $query->paginate(15)->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $tenantId  = auth()->user()->tenant_id;
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $staff     = Staff::where('is_active', true)->orderBy('name')->get();
        $services  = Service::where('is_active', true)->orderBy('name')->get();
        $combos    = ServiceCombo::with('services')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->filter(fn($c) => $c->isLive())
            ->map(fn($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'combo_price'   => round($c->combo_price, 2),
                'savings'       => round($c->savings, 2),
                'service_ids'   => $c->services->pluck('id')->values()->all(),
                'service_names' => $c->services->pluck('name')->values()->all(),
            ])
            ->values();

        return view('appointments.create', compact('customers', 'staff', 'services', 'combos'));
    }

    public function store(Request $request, AvailabilityService $availability, BookingNotificationService $notifications)
    {
        $data = $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'staff_id'      => 'nullable|exists:staff,id',
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'required|exists:services,id',
            'scheduled_at'  => 'required|date|after:now',
            'status'        => 'required|in:pending,confirmed,completed,cancelled,no_show,tentative,awaiting_payment',
            'notes'         => 'nullable|string|max:1000',
            'headcount'     => 'nullable|integer|min:1',
            'venue'         => 'nullable|string|max:255',
            'event_type'    => 'nullable|string|max:50',
            'dietary_notes' => 'nullable|string|max:1000',
            'theme_notes'   => 'nullable|string|max:1000',
            'setup_at'      => 'nullable|date',
            'breakdown_at'  => 'nullable|date',
            'combo_id'          => 'nullable|integer',
            'duration_override' => 'nullable|integer|min:1|max:1440',
        ]);

        $tenantId         = auth()->user()->tenant_id;
        $comboIdInput     = $data['combo_id'] ?? null;
        $durationOverride = isset($data['duration_override']) ? (int) $data['duration_override'] : null;
        unset($data['combo_id'], $data['duration_override']);

        // Resolve combo and merge its services into the selection
        $combo      = null;
        $comboId    = null;
        $comboPrice = null;

        if ($comboIdInput) {
            $combo = ServiceCombo::with('services')
                ->where('tenant_id', $tenantId)
                ->find($comboIdInput);

            if (!$combo || !$combo->isLive()) {
                return back()->withInput()->withErrors(['combo_id' => 'This combo deal is no longer available.']);
            }

            $comboId    = $combo->id;
            $comboPrice = $combo->combo_price;

            $comboServiceIds     = $combo->services->pluck('id')->map(fn($id) => (string) $id)->all();
            $data['service_ids'] = array_values(array_unique(array_merge($data['service_ids'], $comboServiceIds)));
        }

        $services      = Service::findMany($data['service_ids']);
        $totalDuration = $durationOverride ?? $services->sum('duration_minutes');

        if ($services->count() !== count($data['service_ids'])) {
            return back()->withInput()->withErrors(['service_ids' => 'One or more selected services are invalid.']);
        }

        $conflictWarning = null;
        if ($data['staff_id']) {
            $staff    = Staff::findOrFail($data['staff_id']);
            $start    = Carbon::parse($data['scheduled_at']);
            $hardStop = $availability->checkHardStop($staff, $start, $totalDuration);

            if ($hardStop) {
                return back()->withInput()->withErrors(['scheduled_at' => $hardStop]);
            }

            $conflicts = $availability->getConflicts($staff, $start, $totalDuration);
            if ($conflicts->isNotEmpty()) {
                $conflictWarning = $availability->buildConflictWarning($staff, $start, $totalDuration, $conflicts);
            }
        }

        $customer = Customer::findOrFail($data['customer_id']);

        $appointment = DB::transaction(function () use (
            $data, $services, $customer, $totalDuration, $comboId, $comboPrice
        ) {
            $appt = Appointment::create([
                ...$data,
                'tenant_id'        => $customer->tenant_id ?? auth()->user()->tenant_id,
                'duration_minutes' => $totalDuration,
                'combo_id'         => $comboId,
                'combo_price'      => $comboPrice,
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

        if (in_array($data['status'], ['confirmed', 'pending'])) {
            $appointment->load(['customer', 'staff', 'services']);

            $booker        = auth()->user();
            $customerEmail = $appointment->customer->email;
            $bookerEmail   = $booker->email;

            if ($customerEmail) {
                Mail::to($customerEmail)
                    ->queue(new AppointmentConfirmationEmail($appointment, recipient: 'customer'));
            }

            if ($bookerEmail && $bookerEmail !== $customerEmail) {
                Mail::to($bookerEmail)
                    ->queue(new AppointmentConfirmationEmail($appointment, recipient: 'booker'));
            }

            Notification::send(
                collect([$appointment->customer->user, $booker])->filter()->unique('id'),
                new AppointmentBookedNotification($appointment, booker: $booker)
            );
        }

        $notifications->notifyAppointmentCreated($appointment);

        $redirect = $conflictWarning
            ? redirect()->route('appointments.show', $appointment)->with('conflict_warning', $conflictWarning)
            : redirect()->route('appointments.index')->with('success', 'Appointment booked successfully.');

        return $redirect;
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['customer', 'staff', 'services', 'reminders']);
        $hasPos = auth()->user()->tenant?->hasModule('pos') ?? false;

        $timingConflicts = collect();
        if ($appointment->actual_duration_minutes && $appointment->staff_id) {
            $actualEnd = $appointment->scheduled_at->copy()->addMinutes($appointment->actual_duration_minutes);
            $timingConflicts = Appointment::where('tenant_id', $appointment->tenant_id)
                ->where('staff_id', $appointment->staff_id)
                ->where('id', '!=', $appointment->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->where('scheduled_at', '>=', $appointment->scheduled_at)
                ->where('scheduled_at', '<', $actualEnd)
                ->with('customer')
                ->orderBy('scheduled_at')
                ->get();
        }

        return view('appointments.show', compact('appointment', 'hasPos', 'timingConflicts'));
    }

    public function setActualDuration(Request $request, Appointment $appointment)
    {
        $request->validate([
            'actual_duration_minutes' => 'required|integer|min:1|max:1440',
        ]);

        $appointment->update([
            'actual_duration_minutes' => $request->actual_duration_minutes,
        ]);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Actual duration saved.');
    }

    public function clientHistory(Request $request)
    {
        $request->validate([
            'customer_id'   => 'required|integer',
            'service_ids'   => 'required|array',
            'service_ids.*' => 'integer',
        ]);

        $tenantId   = auth()->user()->tenant_id;
        $customerId = (int) $request->customer_id;
        $serviceIds = array_map('intval', $request->service_ids);

        $history = Appointment::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->whereNotNull('actual_duration_minutes')
            ->whereHas('services', fn($q) => $q->whereIn('services.id', $serviceIds))
            ->get(['actual_duration_minutes', 'duration_minutes']);

        if ($history->count() < 2) {
            return response()->json(['sufficient_data' => false]);
        }

        $customer = Customer::find($customerId);

        return response()->json([
            'sufficient_data' => true,
            'avg_actual'      => (int) round($history->avg('actual_duration_minutes')),
            'avg_booked'      => (int) round($history->avg('duration_minutes')),
            'booking_count'   => $history->count(),
            'customer_name'   => $customer?->name,
        ]);
    }

    public function edit(Appointment $appointment, AvailabilityService $availability)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $staff     = Staff::where('is_active', true)->orderBy('name')->get();
        $services  = Service::where('is_active', true)->orderBy('name')->get();

        // Pass the appointment's currently attached service IDs to the view
        // so the form can pre-select them
        $selectedServiceIds = $appointment->services->pluck('id')->all();

        $totalDuration = $appointment->services->sum('duration_minutes');

        $staffAvailability = $staff->map(function (Staff $member) use ($availability, $appointment, $totalDuration) {
            $reason = $availability->check($member, $appointment->scheduled_at, $totalDuration, $appointment->id);

            return [
                'id'        => $member->id,
                'name'      => $member->name,
                'available' => $reason === null,
                'reason'    => $reason,
                'selected'  => $member->id === $appointment->staff_id,
            ];
        });

        return view('appointments.edit', compact(
            'appointment', 'customers', 'staff', 'services', 'staffAvailability', 'selectedServiceIds'
        ));
    }

    public function availability(Request $request, Appointment $appointment, AvailabilityService $availability)
    {
        $data = $request->validate([
            'scheduled_at'  => 'required|date',
            'service_ids'   => 'required|array|min:1',   // service_id → service_ids
            'service_ids.*' => 'required|exists:services,id',
        ]);

        $totalDuration = Service::findMany($data['service_ids'])->sum('duration_minutes');

        $staff           = Staff::where('is_active', true)->orderBy('name')->get();
        $scheduledAt     = Carbon::parse($data['scheduled_at']);

        $availabilityData = $staff->map(function (Staff $member) use ($availability, $scheduledAt, $totalDuration, $appointment) {
            $reason = $availability->check($member, $scheduledAt, $totalDuration, $appointment->id);

            return [
                'id'        => $member->id,
                'name'      => $member->name,
                'available' => $reason === null,
                'reason'    => $reason,
            ];
        });

        return response()->json(['staffAvailability' => $availabilityData]);
    }

    public function update(Request $request, Appointment $appointment, AvailabilityService $availability, BookingNotificationService $notifications)
    {
        $data = $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'staff_id'      => 'nullable|exists:staff,id',
            'service_ids'   => 'required|array|min:1',   // service_id → service_ids
            'service_ids.*' => 'required|exists:services,id',
            'scheduled_at'  => 'required|date',
            'status'        => 'required|in:pending,confirmed,completed,cancelled,no_show,tentative,awaiting_payment',
            'notes'         => 'nullable|string|max:1000',
            'headcount'     => 'nullable|integer|min:1',
            'venue'         => 'nullable|string|max:255',
            'event_type'    => 'nullable|string|max:50',
            'dietary_notes' => 'nullable|string|max:1000',
            'theme_notes'   => 'nullable|string|max:1000',
            'setup_at'      => 'nullable|date',
            'breakdown_at'  => 'nullable|date',
        ]);

        $services      = Service::findMany($data['service_ids']);
        $totalDuration = $services->sum('duration_minutes');

        $slotChanged     = $appointment->scheduled_at->toDateTimeString() !== Carbon::parse($data['scheduled_at'])->toDateTimeString();
        $staffChanged    = $appointment->staff_id !== (int) ($data['staff_id'] ?? 0);
        $durationChanged = $appointment->duration_minutes !== $totalDuration;

        if ($slotChanged || $durationChanged) {
            $data['staff_id'] = null;
        }

        $conflictWarning = null;
        if (!$slotChanged && !$durationChanged && $data['staff_id']) {
            $staff    = Staff::findOrFail($data['staff_id']);
            $start    = Carbon::parse($data['scheduled_at']);
            $hardStop = $availability->checkHardStop($staff, $start, $totalDuration);

            if ($hardStop) {
                return back()->withInput()->withErrors(['staff_id' => $hardStop]);
            }

            $conflicts = $availability->getConflicts($staff, $start, $totalDuration, $appointment->id);
            if ($conflicts->isNotEmpty()) {
                $conflictWarning = $availability->buildConflictWarning($staff, $start, $totalDuration, $conflicts);
            }
        }

        $appointment->update([
            ...$data,
            'duration_minutes' => $totalDuration,
        ]);

        // Re-sync services, preserving price snapshots for unchanged ones
        $existingPivots = $appointment->services->keyBy('id');

        $appointment->services()->sync(
            $services->mapWithKeys(fn($service, $index) => [
                $service->id => [
                    'duration_minutes' => $service->duration_minutes,
                    // Keep original price snapshot if this service was already on the appointment
                    'price_at_booking' => $existingPivots->get($service->id)?->pivot->price_at_booking
                                          ?? $service->price,
                    'sort_order'       => $index,
                ],
            ])->all()
        );

        $notifications->notifyAppointmentUpdated($appointment, array_filter([
            $slotChanged     ? 'rescheduled'      : null,
            $staffChanged    ? 'staff changed'    : null,
            $durationChanged ? 'duration changed' : null,
        ]));

        $successMsg = $slotChanged || $durationChanged
            ? 'Appointment rescheduled. Staff assignment cleared — please assign a staff member.'
            : 'Appointment updated.';

        return redirect()->route('appointments.show', $appointment)
            ->with('success', $successMsg)
            ->with('conflict_warning', $conflictWarning);
    }

    public function assign(Request $request, Appointment $appointment, AvailabilityService $availability, BookingNotificationService $notifications)
    {
        $data = $request->validate([
            'staff_id' => 'required|exists:staff,id',
        ]);

        $staff    = Staff::findOrFail($data['staff_id']);
        $start    = $appointment->scheduled_at;
        $duration = $appointment->duration_minutes;
        $hardStop = $availability->checkHardStop($staff, $start, $duration);

        if ($hardStop) {
            return back()->withErrors(['staff_id' => $hardStop]);
        }

        $conflicts       = $availability->getConflicts($staff, $start, $duration, $appointment->id);
        $conflictWarning = $conflicts->isNotEmpty()
            ? $availability->buildConflictWarning($staff, $start, $duration, $conflicts)
            : null;

        $appointment->update([
            'staff_id' => $staff->id,
            'status'   => $appointment->status === 'pending' ? 'confirmed' : $appointment->status,
        ]);

        $notifications->notifyAppointmentAssigned($appointment);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "{$staff->name} assigned. Appointment confirmed.")
            ->with('conflict_warning', $conflictWarning);
    }

    public function calendar(?string $date = null)
    {
        $week = $date ? Carbon::parse($date)->startOfWeek() : Carbon::now()->startOfWeek();
        $days = collect(range(0, 6))->map(fn($i) => $week->copy()->addDays($i));
        $prev = $week->copy()->subWeek()->toDateString();
        $next = $week->copy()->addWeek()->toDateString();

        $appointments = Appointment::with(['customer', 'staff', 'services'])  // service → services
            ->whereBetween('scheduled_at', [$week, $week->copy()->endOfWeek()])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn($a) => $a->scheduled_at->format('Y-m-d'));

        $hours = collect(range(7, 20))->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00');

        $staff = Staff::where('is_active', true)->orderBy('name')->get();

        $unassigned = Appointment::whereNull('staff_id')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '>=', now())
            ->with(['customer', 'services'])  // service → services
            ->orderBy('scheduled_at')
            ->count();

        return view('appointments.calendar', compact(
            'days', 'appointments', 'hours', 'week', 'prev', 'next', 'staff', 'unassigned'
        ));
    }

    public function remind(Appointment $appointment)
    {
        $appointment->load(['customer', 'staff', 'services']);

        // Calculate amount due (combo + extras, or sum of service prices)
        $comboServiceIds = [];
        if ($appointment->combo_id) {
            $combo = \App\Models\ServiceCombo::with('services')->find($appointment->combo_id);
            $comboServiceIds = $combo ? $combo->services->pluck('id')->all() : [];
        }
        $fullTotal  = $appointment->services->sum(fn($s) => (float)($s->pivot->price_at_booking ?? $s->price));
        $extrasCost = $appointment->combo_price
            ? $appointment->services
                ->filter(fn($s) => !in_array($s->id, $comboServiceIds))
                ->sum(fn($s) => (float)($s->pivot->price_at_booking ?? $s->price))
            : 0;
        $amountDue = $appointment->combo_price
            ? (float)$appointment->combo_price + $extrasCost
            : $fullTotal;
        $amountDue -= (float)($appointment->promo_discount ?? 0);

        // Notify the staff member (in-app) + mail the customer
        $notification = new PaymentReminderNotification($appointment, $amountDue);

        if ($appointment->customer->email) {
            $appointment->customer->notify($notification);
        }

        // Also fire an in-app notification for the logged-in staff/admin
        auth()->user()->notify($notification);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Reminder sent via email and notification.');
    }

    public function markPaid(Appointment $appointment)
    {
        abort_if($appointment->status !== 'awaiting_payment', 422, 'This appointment is not awaiting payment.');

        $appointment->update(['status' => 'completed']);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Payment received — appointment marked as completed.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted.');
    }
}