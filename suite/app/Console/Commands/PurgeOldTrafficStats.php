<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeOldTrafficStats extends Command
{
    protected $signature = 'traffic:purge';

    protected $description = 'Delete page views and clicks older than the retention period promised in the privacy policy';

    public function handle(): int
    {
        $cutoff = now()->subDays(config('analytics.retention_days'));

        // Clicks first so nothing is left pointing at a page view that is gone.
        $clicks = DB::table('click_events')->where('created_at', '<', $cutoff)->delete();
        $views = DB::table('page_views')->where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$views} page view(s) and {$clicks} click(s) older than " . config('analytics.retention_days') . ' days.');

        return Command::SUCCESS;
    }
}
