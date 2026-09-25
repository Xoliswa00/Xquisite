<?php

namespace App\Console\Commands;

use App\Models\FoundingTwentyApplication;
use Illuminate\Console\Command;

class PurgeStaleFoundingTwentyApplications extends Command
{
    protected $signature = 'founding-twenty:purge-stale {--months=12 : Age at which unselected applications are deleted} {--force : Actually delete (default is a dry run)}';

    protected $description = 'Delete unfinished, rejected and waitlisted Founding 20 applications older than the retention period promised in the privacy policy';

    public function handle(): int
    {
        $cutoff = now()->subMonths((int) $this->option('months'));

        // Never touches anyone selected, onboarded or converted.
        $stale = FoundingTwentyApplication::where('created_at', '<', $cutoff)
            ->where(function ($q) {
                $q->whereNull('submitted_at')->orWhereIn('status', ['rejected', 'waitlisted']);
            })
            ->whereNull('tenant_id')
            ->get();

        foreach ($stale as $application) {
            $this->line(($this->option('force') ? 'Deleting' : 'Would delete') . " #{$application->id} {$application->business_name} ({$application->status}, {$application->created_at->toDateString()})");
            if ($this->option('force')) {
                $application->delete();
            }
        }

        $this->info($stale->count() . ($this->option('force') ? ' deleted.' : ' would be deleted. Re-run with --force to delete.'));

        return Command::SUCCESS;
    }
}
