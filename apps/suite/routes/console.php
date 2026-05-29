<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Retry pending billing sync items every 5 minutes.
Schedule::command('billing:sync-queue')->everyFiveMinutes()->withoutOverlapping();

// Send appointment reminder emails every 15 minutes.
// Reminders are created by AppointmentObserver 24h and 1h before each appointment.
Schedule::command('booking:send-reminders')->everyFifteenMinutes()->withoutOverlapping();
