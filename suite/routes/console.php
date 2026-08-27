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

// Ping all active monitored instances every 5 minutes and log health status.
Schedule::command('instances:check-health')->everyFiveMinutes()->withoutOverlapping();

// Property Management: generate each active lease's rent payment on the 1st of the month.
Schedule::command('rent:generate-monthly')->monthlyOn(1, '02:00')->withoutOverlapping();

// Property Management: flag pending rent payments/charges past their due date.
Schedule::command('rent:flag-overdue')->dailyAt('01:00')->withoutOverlapping();

// Property Management: check for data desyncs and a stalled billing cycle; logs via
// Log::error(), which the 'database' log channel already turns into an admin email alert.
// Runs after flag-overdue so a non-zero "pending past due" finding actually means
// something (flag-overdue didn't run), not just routine same-tick duplicate work.
Schedule::command('property:health-check')->dailyAt('01:30')->withoutOverlapping();

// Property Management: notify tenant owners about leases ending in 30/14/7/1 days.
Schedule::command('leases:check-expiring')->dailyAt('07:00')->withoutOverlapping();

// Property Management: remind renters whose rent is due within the next few days.
Schedule::command('rent:send-due-reminders')->dailyAt('08:00')->withoutOverlapping();
