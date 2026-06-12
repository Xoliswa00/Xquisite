<?php

namespace App\Console\Commands;

use App\Services\PlatformBillingService;
use Illuminate\Console\Command;

class CheckPlatformBilling extends Command
{
    protected $signature   = 'billing:check';
    protected $description = 'Run daily platform billing checks: mark overdue, start grace periods, send reminders, suspend after 5 days.';

    public function handle(PlatformBillingService $billing): int
    {
        $this->info('Running platform billing check...');
        $billing->runDailyCheck();
        $this->info('Done.');
        return self::SUCCESS;
    }
}
