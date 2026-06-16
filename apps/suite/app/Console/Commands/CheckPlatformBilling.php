<?php

namespace App\Console\Commands;

use App\Services\PlatformBillingService;
use Illuminate\Console\Command;

class CheckPlatformBilling extends Command
{
    protected $signature   = 'billing:check';
    protected $description = 'Run daily platform billing checks (overdue → grace → suspension)';

    public function handle(PlatformBillingService $billing): int
    {
        $this->info('Running platform billing check…');
        $billing->runDailyCheck();
        $this->info('Done.');
        return self::SUCCESS;
    }
}
