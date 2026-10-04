<?php

namespace App\Console\Commands;

use App\Modules\Booking\Models\Appointment;
use App\Services\Notifications\BookingNotificationService;
use App\Services\Tenant\TenantContext;
use Illuminate\Console\Command;

/**
 * "Time for your next ...": once a completed appointment's service is due
 * again (services.rebook_after_days), nudge the client once, in-app, by push
 * and by email, linking to "Book this look again" when they have a saved look.
 *
 * Each completed appointment is looked at once: rebook_reminded_at is set
 * whether a reminder was sent or deliberately skipped, because:
 *  - the client already booked again after it (no nag);
 *  - they opted out, or their account is inactive;
 *  - it's more than STALE_AFTER_DAYS past due (the first run after switching
 *    the setting on must not message every client the business ever had).
 */
class SendRebookReminders extends Command
{
    protected $signature   = 'booking:send-rebook-reminders';
    protected $description = 'Remind clients when a service they had is due again';

    public const STALE_AFTER_DAYS = 14;

    public function handle(BookingNotificationService $notifications): int
    {
        $sent = 0;
        $skipped = 0;

        Appointment::withoutGlobalScopes()
            ->where('status', 'completed')
            ->whereNull('rebook_reminded_at')
            ->whereHas('services', fn ($q) => $q->withoutGlobalScopes()->whereNotNull('rebook_after_days'))
            ->with(['services', 'customer', 'tenant', 'lookPhotos'])
            ->chunkById(200, function ($appointments) use ($notifications, &$sent, &$skipped) {
                foreach ($appointments as $appointment) {
                    $days = $appointment->services->whereNotNull('rebook_after_days')->min('rebook_after_days');
                    $due  = $appointment->scheduled_at->copy()->addDays($days);

                    if ($due->isFuture()) {
                        continue; // not yet; look again tomorrow
                    }

                    $reason = $this->skipReason($appointment, $due);
                    if ($reason === null) {
                        TenantContext::set($appointment->tenant_id);
                        $notifications->notifyRebookDue($appointment, $this->bookUrl($appointment));
                        $sent++;
                    } else {
                        $skipped++;
                    }

                    $appointment->update(['rebook_reminded_at' => now()]);
                }
            });

        $this->info("Sent {$sent} rebook reminder(s), skipped {$skipped}.");

        return self::SUCCESS;
    }

    private function skipReason(Appointment $appointment, $due): ?string
    {
        $customer = $appointment->customer;

        if (! $customer || ! $customer->wantsRebookReminders()) {
            return 'opted out or inactive';
        }
        if (! $appointment->tenant?->is_active) {
            return 'business inactive';
        }
        if ($due->lt(now()->subDays(self::STALE_AFTER_DAYS))) {
            return 'stale';
        }

        $bookedAgain = Appointment::withoutGlobalScopes()
            ->where('customer_id', $customer->id)
            ->where('id', '!=', $appointment->id)
            ->where('scheduled_at', '>', $appointment->scheduled_at)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();

        return $bookedAgain ? 'already rebooked' : null;
    }

    /** Saved look: My Bookings (where "Book this look again" lives). Otherwise straight to the same services. */
    private function bookUrl(Appointment $appointment): string
    {
        $slug = $appointment->tenant->slug;

        return $appointment->isLookSaved()
            ? route('book.my-bookings', $slug)
            : route('book.service', ['slug' => $slug, 'service_ids' => $appointment->services->where('is_active', true)->pluck('id')->values()->all()]);
    }
}
