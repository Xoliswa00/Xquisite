<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset demo tenant every 4 hours
        $schedule->command('demo:reset --force')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();

        // Check instance health every 5 minutes
        $schedule->command('instances:check-health')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Instance health check failed');
            });

        $schedule->job(new \App\Jobs\ReportHealthStatus)->everyFiveMinutes();

        // Send appointment reminder emails that are due
        $schedule->command('booking:send-reminders')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
