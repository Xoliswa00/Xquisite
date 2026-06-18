<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderEmail;
use App\Modules\Booking\Models\AppointmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature   = 'booking:send-reminders {--dry-run : Show what would be sent without sending}';
    protected $description = 'Send pending appointment reminder emails that are due.';

    public function handle(): int
    {
        $due = AppointmentReminder::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with(['appointment.customer', 'appointment.service', 'appointment.staff'])
            ->get();

        if ($due->isEmpty()) {
            $this->info('No reminders due.');
            return self::SUCCESS;
        }

        $this->info("Processing {$due->count()} reminder(s)…");

        $sent   = 0;
        $skipped = 0;

        foreach ($due as $reminder) {
            $appt = $reminder->appointment;

            // Skip if appointment no longer active
            if (!$appt || in_array($appt->status, ['cancelled', 'no_show', 'completed'])) {
                $reminder->update(['status' => 'skipped', 'sent_at' => now()]);
                $skipped++;
                continue;
            }

            // Skip if customer has no email
            if (!$appt->customer?->email) {
                $reminder->update(['status' => 'skipped', 'sent_at' => now()]);
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [dry-run] Would send {$reminder->type} reminder to {$appt->customer->email} for {$appt->scheduled_at->format('d M H:i')}");
                $sent++;
                continue;
            }

            try {
                Mail::to($appt->customer->email)
                    ->queue(new AppointmentReminderEmail($appt, $reminder->type));

                $reminder->update(['status' => 'sent', 'sent_at' => now()]);
                $this->line("  <fg=green>✓</> {$reminder->type} reminder → {$appt->customer->email}");
                $sent++;

            } catch (\Throwable $e) {
                $this->line("  <fg=red>✗</> Failed for appointment #{$appt->id}: {$e->getMessage()}");
                // Leave as pending — will retry on next run
            }
        }

        $this->info("Done — {$sent} sent, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
