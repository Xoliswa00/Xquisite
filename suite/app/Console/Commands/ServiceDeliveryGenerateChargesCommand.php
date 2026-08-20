<?php

namespace App\Console\Commands;

use App\Modules\ServiceDelivery\Models\ServiceAgreement;
use Illuminate\Console\Command;

class ServiceDeliveryGenerateChargesCommand extends Command
{
    protected $signature   = 'service-delivery:generate-charges';
    protected $description = 'Generate the current period charge for every active service agreement';

    public function handle(): int
    {
        $this->info('Generating service agreement charges for ' . now()->format('F Y') . '…');

        $agreements = ServiceAgreement::where('status', 'active')->get();
        $created = 0;

        foreach ($agreements as $agreement) {
            $charge = $agreement->generateCurrentPeriodCharge();
            if ($charge->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info("Done — {$created} new charge(s) created.");
        return self::SUCCESS;
    }
}
