<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Services\Booking\AppointmentQuoteService;
use App\Services\Notifications\BookingNotificationService;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Quotes from inspiration photos: staff send a price and time, the client
 * accepts or declines from My Bookings. See AppointmentQuoteService.
 */
class AppointmentQuoteController extends Controller
{
    public function __construct(
        private readonly AppointmentQuoteService $quotes,
        private readonly BookingNotificationService $notifications,
    ) {}

    // ── Staff ───────────────────────────────────────────────────────────────

    public function send(Request $request, Appointment $appointment)
    {
        abort_unless((int) $appointment->tenant_id === (int) auth()->user()->tenant_id, 404);

        if (! in_array($appointment->status, ['pending', 'confirmed', 'tentative'], true) || $appointment->scheduled_at->isPast()) {
            return back()->withErrors(['quote' => 'Quotes can only be sent for upcoming bookings.']);
        }
        if ($appointment->quote_status === AppointmentQuoteService::ACCEPTED) {
            return back()->withErrors(['quote' => 'The client already accepted a quote for this booking.']);
        }

        $data = $request->validate([
            'quoted_price'            => 'required|numeric|min:0|max:1000000',
            'quoted_duration_minutes' => 'required|integer|min:5|max:43200',
            'quote_note'              => 'nullable|string|max:1000',
        ]);

        $appointment->loadMissing(['services', 'customer']);
        $fits = $this->quotes->send($appointment, (float) $data['quoted_price'], (int) $data['quoted_duration_minutes'], $data['quote_note'] ?? null);
        $this->notifications->notifyQuoteSent($appointment);

        $redirect = back()->with('success', 'Quote sent to ' . ($appointment->customer?->name ?? 'the client') . '.');

        return $fits ? $redirect : $redirect->with('warning',
            'The quoted time runs past what is free after ' . $appointment->scheduled_at->format('H:i') . '. You may need to move this booking or another one.');
    }

    // ── Client ──────────────────────────────────────────────────────────────

    private function customerAppointment(string $slug, Appointment $appointment): Appointment
    {
        $tenant   = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        $customer = Auth::guard('customer')->user();

        abort_unless(
            $customer
            && (int) $appointment->tenant_id === (int) $tenant->id
            && (int) $appointment->customer_id === (int) $customer->id,
            404
        );

        return $appointment;
    }

    public function accept(string $slug, Appointment $appointment)
    {
        $appointment = $this->customerAppointment($slug, $appointment);

        if ($appointment->quote_status !== AppointmentQuoteService::SENT || $appointment->scheduled_at->isPast()) {
            return back()->withErrors(['quote' => 'This quote can no longer be accepted.']);
        }
        // Past the deadline but the expiry job hasn't run yet: same answer either way.
        if ($this->quotes->hasExpired($appointment)) {
            return back()->withErrors(['quote' => 'This quote has expired. You can book again any time and ask for a new one.']);
        }

        $this->quotes->accept($appointment);
        $this->notifications->notifyQuoteAnswered($appointment, accepted: true);

        return back()->with('success', 'Quote accepted. Your booking is updated with the new price and time.');
    }

    public function decline(string $slug, Appointment $appointment)
    {
        $appointment = $this->customerAppointment($slug, $appointment);

        if ($appointment->quote_status !== AppointmentQuoteService::SENT) {
            return back()->withErrors(['quote' => 'This quote can no longer be declined.']);
        }

        $this->quotes->decline($appointment);
        $this->notifications->notifyQuoteAnswered($appointment, accepted: false);

        return back()->with('success', 'Quote declined and the booking cancelled. Nothing to pay.');
    }
}
