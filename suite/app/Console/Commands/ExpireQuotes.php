<?php

namespace App\Console\Commands;

use App\Modules\Booking\Models\Appointment;
use App\Services\Booking\AppointmentQuoteService;
use App\Services\Notifications\BookingNotificationService;
use App\Services\Tenant\TenantContext;
use Illuminate\Console\Command;

/**
 * Keeps quotes from holding slots forever. For every quote that's been sent
 * and not answered:
 *  - past its deadline: cancel the booking, free the slot, tell both sides;
 *  - halfway to its deadline: send the client one "expires soon" nudge.
 * See AppointmentQuoteService::deadlineFor() for how the deadline is set.
 */
class ExpireQuotes extends Command
{
    protected $signature   = 'booking:expire-quotes';
    protected $description = 'Remind clients about quotes that expire soon, and cancel bookings whose quote went unanswered';

    public function handle(AppointmentQuoteService $quotes, BookingNotificationService $notifications): int
    {
        $expired = 0;
        $reminded = 0;

        Appointment::withoutGlobalScopes()
            ->where('quote_status', AppointmentQuoteService::SENT)
            ->whereNotNull('quote_expires_at')
            ->with(['customer', 'services', 'staff', 'tenant'])
            ->chunkById(200, function ($appointments) use ($quotes, $notifications, &$expired, &$reminded) {
                foreach ($appointments as $appointment) {
                    TenantContext::set($appointment->tenant_id);

                    if ($quotes->hasExpired($appointment)) {
                        $quotes->expire($appointment);
                        $notifications->notifyQuoteExpired($appointment);
                        $expired++;
                    } elseif ($quotes->reminderDue($appointment)) {
                        $appointment->update(['quote_reminded_at' => now()]);
                        $notifications->notifyQuoteExpiring($appointment);
                        $reminded++;
                    }
                }
            });

        $this->info("Expired {$expired} quote(s), reminded {$reminded}.");

        return self::SUCCESS;
    }
}
