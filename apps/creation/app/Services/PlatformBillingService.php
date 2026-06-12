<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PlatformInvoice;
use App\Models\User;
use App\Notifications\BillingGracePeriodExpiringNotification;
use App\Notifications\BillingGracePeriodStartedNotification;
use App\Notifications\BillingInvoiceCreatedNotification;
use App\Notifications\BillingServiceReactivatedNotification;
use App\Notifications\BillingServiceSuspendedNotification;
use Carbon\Carbon;

class PlatformBillingService
{
    const GRACE_DAYS = 5;

    /**
     * Generate a monthly platform invoice for a company.
     */
    public function generateInvoice(Company $company): PlatformInvoice
    {
        $plan   = $company->subscription_plan ?? $company->plan ?? 'basic';
        $amount = Company::planAmount($plan);
        $now    = Carbon::now();

        $invoice = PlatformInvoice::create([
            'company_id'           => $company->id,
            'invoice_number'       => PlatformInvoice::generateNumber(),
            'plan'                 => $plan,
            'amount'               => $amount,
            'status'               => 'unpaid',
            'due_date'             => $now->copy()->addDays(7),
            'billing_period_start' => $now->copy()->startOfMonth(),
            'billing_period_end'   => $now->copy()->endOfMonth(),
        ]);

        $company->update(['last_billing_date' => $now]);

        // Notify the platform owner
        $owner = $company->user;
        if ($owner) {
            $owner->notify(new BillingInvoiceCreatedNotification($invoice));
        }

        return $invoice;
    }

    /**
     * Run the daily billing check:
     * - Mark invoices overdue
     * - Start grace periods
     * - Send day-2 and day-1 reminders
     * - Suspend after grace expires
     */
    public function runDailyCheck(): void
    {
        // 1. Mark unpaid invoices as overdue where due_date has passed
        PlatformInvoice::where('status', 'unpaid')
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => 'overdue']);

        // 2. For newly overdue invoices (no grace started yet), start grace period
        $newlyOverdue = PlatformInvoice::where('status', 'overdue')
            ->whereHas('company', fn($q) => $q->whereNull('grace_period_ends_at')->where('status', 'active'))
            ->with('company.user')
            ->get();

        foreach ($newlyOverdue as $invoice) {
            $company = $invoice->company;
            $graceEnd = now()->addDays(self::GRACE_DAYS);

            $company->update(['grace_period_ends_at' => $graceEnd]);

            if ($owner = $company->user) {
                $owner->notify(new BillingGracePeriodStartedNotification($invoice, self::GRACE_DAYS));
            }
        }

        // 3. Send reminders for companies in grace period
        $inGrace = Company::where('status', 'active')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '>', now())
            ->with(['user', 'platformInvoices' => fn($q) => $q->where('status', 'overdue')->latest()])
            ->get();

        foreach ($inGrace as $company) {
            $daysLeft = (int) now()->diffInDays($company->grace_period_ends_at, false);
            $invoice  = $company->platformInvoices->first();

            if (!$invoice) continue;

            // Remind at 2 days left and 1 day left
            if (in_array($daysLeft, [2, 1])) {
                if ($owner = $company->user) {
                    $owner->notify(new BillingGracePeriodExpiringNotification($invoice, $daysLeft));
                }
            }
        }

        // 4. Suspend companies whose grace period has expired
        $expired = Company::where('status', 'active')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '<=', now())
            ->with(['user', 'platformInvoices' => fn($q) => $q->where('status', 'overdue')->latest()])
            ->get();

        foreach ($expired as $company) {
            $invoice = $company->platformInvoices->first();

            $company->update([
                'status'               => 'suspended',
                'suspended_at'         => now(),
                'grace_period_ends_at' => null,
            ]);

            if ($invoice && $owner = $company->user) {
                $owner->notify(new BillingServiceSuspendedNotification($invoice));
            }
        }
    }

    /**
     * Record a payment and reactivate company if suspended.
     */
    public function recordPayment(
        PlatformInvoice $invoice,
        string $method,
        string $reference
    ): PlatformInvoice {
        $invoice->update([
            'status'            => 'paid',
            'paid_at'           => now(),
            'payment_method'    => $method,
            'payment_reference' => $reference,
        ]);

        $company = $invoice->company;
        $wasSuspended = $company->status === 'suspended';

        // Clear grace / suspension state
        $company->update([
            'status'               => 'active',
            'grace_period_ends_at' => null,
            'suspended_at'         => null,
            'subscription_renewal_date' => now()->addMonth()->toDateString(),
        ]);

        if ($wasSuspended) {
            if ($owner = $company->user) {
                $owner->notify(new BillingServiceReactivatedNotification($invoice));
            }
        }

        return $invoice->fresh();
    }
}
