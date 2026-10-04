<?php

namespace App\Services\Booking;

use App\Modules\Booking\Models\Appointment;
use Illuminate\Support\Facades\DB;

/**
 * "Confirm the price and time from the client's photos" for services that
 * can't be priced up front (long braids, custom nail art, colour work).
 *
 * requested -> sent -> accepted | declined. The slot is held (pending) the
 * whole time. Accepting writes the quote into the booking itself:
 *  - appointments.duration_minutes, which the availability checks use;
 *  - the services' pivot price/duration, which POS checkout charges from,
 * so the till, the calendar and the client all agree on one number.
 */
class AppointmentQuoteService
{
    public const REQUESTED = 'requested';
    public const SENT      = 'sent';
    public const ACCEPTED  = 'accepted';
    public const DECLINED  = 'declined';

    public function __construct(private readonly AvailabilityService $availability) {}

    /**
     * Staff send (or revise) the quote. Returns false when the quoted time
     * would overlap other bookings at that slot, so staff can be warned; the
     * quote is still sent, because only they know whether that's workable.
     */
    public function send(Appointment $appointment, float $price, int $minutes, ?string $note): bool
    {
        $appointment->update([
            'quote_status'            => self::SENT,
            'quoted_price'            => round($price, 2),
            'quoted_duration_minutes' => $minutes,
            'quote_note'              => $note,
            'quote_sent_at'           => now(),
            'quote_responded_at'      => null,
        ]);

        return $this->fitsSlot($appointment, $minutes);
    }

    public function accept(Appointment $appointment): void
    {
        DB::transaction(function () use ($appointment) {
            $appointment->loadMissing('services');
            $services = $appointment->services;

            // The quote covers the whole booking: fixed-price services keep their
            // own price and time, and the quoted service absorbs the rest.
            $quoted = $services->filter(fn ($s) => $s->requires_quote)->values();
            $quoted = $quoted->isEmpty() ? $services->take(1) : $quoted;
            $fixed  = $services->reject(fn ($s) => $quoted->contains('id', $s->id));

            $fixedPrice    = (float) $fixed->sum(fn ($s) => $s->pivot->price_at_booking);
            $fixedDuration = (int) $fixed->sum(fn ($s) => $s->pivot->duration_minutes);

            foreach ($quoted as $i => $service) {
                $appointment->services()->updateExistingPivot($service->id, $i === 0
                    ? [
                        'price_at_booking' => max(0, (float) $appointment->quoted_price - $fixedPrice),
                        'duration_minutes' => max(0, (int) $appointment->quoted_duration_minutes - $fixedDuration),
                    ]
                    : ['price_at_booking' => 0, 'duration_minutes' => 0]);
            }

            $appointment->update([
                'quote_status'       => self::ACCEPTED,
                'quote_responded_at' => now(),
                'duration_minutes'   => $appointment->quoted_duration_minutes,
            ]);
        });
    }

    public function decline(Appointment $appointment): void
    {
        $appointment->update([
            'quote_status'       => self::DECLINED,
            'quote_responded_at' => now(),
            'status'             => 'cancelled',
        ]);
    }

    /** Multi-day bookings have no fixed slot, so they always "fit". */
    private function fitsSlot(Appointment $appointment, int $minutes): bool
    {
        if ($minutes >= 1440 || $minutes <= $appointment->duration_minutes) {
            return true;
        }

        $slots = $this->availability->availableSlotsForDuration(
            $minutes,
            $appointment->scheduled_at->copy()->startOfDay(),
            $appointment->services->pluck('id')->all(),
            $appointment->id,
        );

        return $slots->contains(fn ($s) => $s->format('Y-m-d H:i') === $appointment->scheduled_at->format('Y-m-d H:i'));
    }
}
