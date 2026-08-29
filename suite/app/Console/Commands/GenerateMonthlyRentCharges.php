<?php

namespace App\Console\Commands;

use App\Services\Property\RentCycleService;
use Illuminate\Console\Command;

class GenerateMonthlyRentCharges extends Command
{
    protected $signature = 'rent:generate-monthly';

    protected $description = 'Generate this month\'s rent payment record for every active lease';

    public function handle(RentCycleService $rentCycle): int
    {
        $created = $rentCycle->generateMonthly();

        $this->info("{$created} new payment record(s) generated for " . now()->format('F Y') . '.');

        return Command::SUCCESS;
    }
}
