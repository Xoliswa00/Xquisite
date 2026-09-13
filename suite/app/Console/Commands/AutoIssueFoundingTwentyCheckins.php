<?php

namespace App\Console\Commands;

use App\Models\FoundingTwentyApplication;
use Illuminate\Console\Command;

class AutoIssueFoundingTwentyCheckins extends Command
{
    protected $signature = 'founding-twenty:auto-issue-checkins';

    protected $description = 'Auto-create 30/60/90-day check-ins once a Founding 20 business has been onboarded that long';

    /**
     * Days-since-onboarded -> check-in type. Checked as "days elapsed >= threshold
     * and not yet issued" rather than an exact-day match, so a scheduler gap doesn't
     * cause a check-in to be silently skipped forever — the next run just catches up.
     */
    private const THRESHOLDS = ['30_day' => 30, '60_day' => 60, '90_day' => 90];

    public function handle(): int
    {
        $issued = 0;

        $applications = FoundingTwentyApplication::whereNotNull('tenant_linked_at')
            ->with('checkins')
            ->get();

        foreach ($applications as $application) {
            $daysSinceLinked = $application->tenant_linked_at->diffInDays(now());
            $existingTypes = $application->checkins->pluck('checkin_type')->all();

            foreach (self::THRESHOLDS as $type => $threshold) {
                if ($daysSinceLinked >= $threshold && !in_array($type, $existingTypes, true)) {
                    $application->checkins()->create(['checkin_type' => $type]);
                    $issued++;
                    $this->line("  Issued {$type} check-in for {$application->business_name}.");
                }
            }
        }

        $this->info("{$issued} check-in(s) auto-issued.");

        return Command::SUCCESS;
    }
}
