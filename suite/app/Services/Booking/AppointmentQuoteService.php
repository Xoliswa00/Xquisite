<?php

namespace App\Services\Booking;

use App\Modules\Booking\Models\Appointment;
use Illuminate\Support\Carbon;
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

    /** The client didn't answer in time; the booking was cancelled and the slot freed. */
    public const EXPIRED   = 'expired';

    public const DEFAULT_EXPIRY_HOURS = 48;

    /** A quote must be answered this long before the appointment, so staff can still refill the slot. */
    public const CUTOFF_BEFORE_START_HOURS = 24;

    /** ...but the client always gets at least this long, even for a late quote. */
    public const MINIMUM_HOURS_TO_ANSWER = 2;

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
            'quote_expires_at'        => $this->deadlineFor($appointment),
            'quote_reminded_at'       => null, // a revised quote gets its own reminder
        ]);

        return $this->fitsSlot($appointment, $minutes);
    }

    /**
     * When a quote sent now must be answered by: the business's expiry window
     * (default 48h) or 24h before the appointment, whichever comes first, so
     * an unanswered quote can't hold a slot nobody else can book. A quote sent
     * late still gives the client a couple of hours, never past the start.
     */
    public function deadlineFor(Appointment $appointment): Carbon
    {
        $hours    = (int) ($appointment->tenant?->quote_expiry_hours ?: self::DEFAULT_EXPIRY_HOURS);
        $start    = $appointment->scheduled_at->copy();
        $deadline = now()->addHours($hours)->min($start->copy()->subHours(self::CUTOFF_BEFORE_START_HOURS));
        $floor    = now()->addHours(self::MINIMUM_HOURS_TO_ANSWER)->min($start);

        return $deadline->max($floor);
    }

    public function hasExpired(Appointment $appointment): bool
    {
        return $appointment->quote_status === self::SENT
            && $appointment->quote_expires_at !== null
            && $appointment->quote_expires_at->isPast();
    }

    /** Halfway to the deadline is when the one "expires soon" nudge goes out. */
    public function reminderDue(Appointment $appointment): bool
    {
        if ($appointment->quote_status !== self::SENT || $appointment->quote_reminded_at
            || ! $appointment->quote_expires_at || ! $appointment->quote_sent_at) {
            return false;
        }

        $halfway = $appointment->quote_sent_at->copy()
            ->addSeconds((int) ($appointment->quote_sent_at->diffInSeconds($appointment->quote_expires_at) / 2));

        return now()->gte($halfway) && $appointment->quote_expires_at->isFuture();
    }

    /** The deadline passed with no answer: cancel the booking so the slot can be sold again. */
    public function expire(Appointment $appointment): void
    {
        $appointment->update([
            'quote_status'       => self::EXPIRED,
            'quote_responded_at' => now(),
            'status'             => 'cancelled',
        ]);
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
