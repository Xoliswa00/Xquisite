<?php

namespace App\Console\Commands;

use App\Modules\ServiceDelivery\Models\ServiceAgreement;
use App\Notifications\ServiceAgreementPaymentReminderNotification;
use App\Notifications\ServiceAgreementSuspendedNotification;
use Illuminate\Console\Command;

class ServiceDeliveryCheckCommand extends Command
{
    protected $signature   = 'service-delivery:check';
    protected $description = 'Advance overdue service agreements through the Day 3/7/10/30 late-payment stages';

    public function handle(): int
    {
        $this->info('Running service delivery late-payment check…');

        $agreements = ServiceAgreement::whereIn('status', ['active', 'suspended'])
            ->with(['client', 'tenant'])
            ->get();

        $advanced = 0;

        foreach ($agreements as $agreement) {
            $charge = $agreement->charges()
                ->whereIn('status', ['pending', 'overdue'])
                ->where('due_date', '<', now())
                ->orderBy('due_date')
                ->first();

            if (!$charge) {
                continue;
            }

            $daysOverdue = (int) $charge->due_date->diffInDays(now());
            $owner = $agreement->tenant?->owner();

            if ($daysOverdue >= 30) {
                if ($agreement->status !== 'terminated') {
                    $agreement->update([
                        'status'             => 'terminated',
                        'terminated_at'      => now(),
                        'termination_reason' => 'Automatic termination — unpaid for 30+ days per SLA policy.',
                    ]);
                    $advanced++;
                }
                continue;
            }

            $targetStage = match (true) {
                $daysOverdue >= 10 => 'suspended',
                $daysOverdue >= 7  => 'reminder_2',
                $daysOverdue >= 3  => 'reminder_1',
                default            => 'current',
            };

            if ($targetStage === $agreement->last_reminder_stage_sent || $targetStage === 'current') {
                continue;
            }

            $updates = ['late_stage' => $targetStage, 'last_reminder_stage_sent' => $targetStage];

            if ($targetStage === 'suspended') {
                $updates['status'] = 'suspended';
                $updates['suspended_at'] = now();
                $agreement->update($updates);
                $owner?->notify(new ServiceAgreementSuspendedNotification($agreement));
            } else {
                $agreement->update($updates);
                $owner?->notify(new ServiceAgreementPaymentReminderNotification($agreement, $charge, $daysOverdue));
            }

            $advanced++;
        }

        $this->info("Done — {$advanced} agreement(s) advanced.");
        return self::SUCCESS;
    }
}
