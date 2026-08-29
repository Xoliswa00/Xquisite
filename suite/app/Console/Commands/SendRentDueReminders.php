<?php

namespace App\Console\Commands;

use App\Modules\Property\Models\RentPayment;
use App\Notifications\RentDueReminderNotification;
use Illuminate\Console\Command;

class SendRentDueReminders extends Command
{
    protected $signature = 'rent:send-due-reminders';

    protected $description = 'Remind renters whose rent is due within the next few days and hasn\'t been reminded yet';

    private const DAYS_BEFORE = 3;

    public function handle(): int
    {
        $payments = RentPayment::where('status', 'pending')
            ->whereNull('reminder_sent_at')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(self::DAYS_BEFORE)->endOfDay()])
            ->with(['renter', 'unit.property'])
            ->get();

        $sent = 0;

        foreach ($payments as $payment) {
            if (!$payment->renter?->email) {
                continue;
            }

            $payment->renter->notify(new RentDueReminderNotification($payment));
            $payment->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("{$sent} rent-due reminder(s) sent.");

        return Command::SUCCESS;
    }
}
