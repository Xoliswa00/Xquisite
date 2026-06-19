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

// Run platform billing checks daily at 08:00 (overdue → grace → suspension).
Schedule::command('billing:check')->dailyAt('08:00')->withoutOverlapping();

// Generate monthly platform invoices on the 1st of each month at 02:00.
Schedule::command('billing:generate')->monthlyOn(1, '02:00')->withoutOverlapping();

// Process billing queue retries (failed invoice generations) every 5 minutes.
Schedule::command('billing:process-queue')->everyFiveMinutes()->withoutOverlapping();

// Cancel abandoned PayFast orders (>30 min) and release their reserved stock.
Schedule::command('ecommerce:expire-pending-orders')->everyTenMinutes()->withoutOverlapping();
