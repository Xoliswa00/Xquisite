<?php

namespace App\Console\Commands;

use App\Services\Property\RentCycleService;
use Illuminate\Console\Command;

class FlagOverdueRentCharges extends Command
{
    protected $signature = 'rent:flag-overdue';

    protected $description = 'Flag pending rent payments and lease charges that are past their due date';

    public function handle(RentCycleService $rentCycle): int
    {
        $count = $rentCycle->flagOverdue();

        $this->info("{$count} payment(s) marked as overdue.");

        return Command::SUCCESS;
    }
}
