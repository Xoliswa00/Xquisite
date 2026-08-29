<?php

namespace App\Console\Commands;

use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\LeaseCharge;
use App\Modules\Property\Models\RentPayment;
use App\Modules\Property\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PropertyHealthCheck extends Command
{
    protected $signature = 'property:health-check';

    protected $description = 'Check the Property Management module for data desyncs and a stalled billing cycle, alerting on anything wrong';

    public function handle(): int
    {
        $issues = 0;

        $issues += $this->check(
            'Unit marked occupied with no active lease',
            Unit::where('status', 'occupied')->whereDoesntHave('activeLease')
        );

        $issues += $this->check(
            'Unit marked vacant but has an active lease',
            Unit::where('status', 'vacant')->whereHas('activeLease')
        );

        $issues += $this->check(
            'Active lease missing this month\'s rent payment (billing cycle may not have run)',
            Lease::where('status', 'active')
                ->whereDoesntHave('rentPayments', fn ($q) => $q->where('period', Lease::currentPeriod()))
        );

        $issues += $this->check(
            'Rent payment pending/overdue past its due date (flag-overdue may not have run)',
            RentPayment::where('status', 'pending')->where('due_date', '<', now())
        );

        $issues += $this->check(
            'Lease charge pending past its due date (flag-overdue may not have run)',
            LeaseCharge::where('status', 'pending')->whereNotNull('due_date')->where('due_date', '<', now())
        );

        $issues += $this->check(
            'Rent payment overpaid beyond amount due',
            RentPayment::whereColumn('amount_paid', '>', 'amount_due')
        );

        $issues += $this->check(
            'Lease charge overpaid beyond amount due',
            LeaseCharge::whereColumn('amount_paid', '>', 'amount_incl')
        );

        if ($issues === 0) {
            $this->info('Property Management health check: all clear.');
        } else {
            $this->warn("Property Management health check: {$issues} issue(s) found and logged.");
        }

        return Command::SUCCESS;
    }

    private function check(string $description, $query): int
    {
        $count = (clone $query)->count();

        if ($count === 0) {
            return 0;
        }

        $ids = (clone $query)->limit(20)->pluck('id')->all();

        Log::error("[property:health-check] {$description}", [
            'count' => $count,
            'ids'   => $ids,
        ]);

        $this->error("{$description}: {$count}");

        return 1;
    }
}
