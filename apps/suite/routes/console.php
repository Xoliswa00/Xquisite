<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Retry pending billing sync items every 5 minutes.
// Safe to run even when billing is down — items stay queued.
Schedule::command('billing:sync-queue')->everyFiveMinutes()->withoutOverlapping();
