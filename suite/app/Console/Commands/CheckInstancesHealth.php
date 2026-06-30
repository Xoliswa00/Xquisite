<?php

namespace App\Console\Commands;

use App\Jobs\CheckInstanceHealth;
use App\Models\MonitoredInstance;
use Illuminate\Console\Command;

class CheckInstancesHealth extends Command
{
    protected $signature = 'instances:check-health';

    protected $description = 'Check health of all monitored instances';

    public function handle(): int
    {
        $instances = MonitoredInstance::where('is_active', true)->get();

        if ($instances->isEmpty()) {
            $this->info('No active instances to check.');
            return Command::SUCCESS;
        }

        $this->info('Checking health of ' . $instances->count() . ' instance(s)...');

        foreach ($instances as $instance) {
            CheckInstanceHealth::dispatchSync($instance);
            $this->line("✓ Checked: {$instance->name}");
        }

        $this->info('All health checks complete.');
        return Command::SUCCESS;
    }
}
