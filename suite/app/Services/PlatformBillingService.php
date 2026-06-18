<?php

namespace App\Services;

use App\Models\BillingSetting;
use App\Models\PlatformInvoice;
use App\Models\Tenant;
use App\Notifications\BillingGracePeriodExpiringNotification;
use App\Notifications\BillingGracePeriodStartedNotification;
use App\Notifications\BillingInvoiceCreatedNotification;
use App\Notifications\BillingServiceReactivatedNotification;
use App\Notifications\BillingServiceSuspendedNotification;
use Illuminate\Database\Eloquent\Collection;

class PlatformBillingService
{
    public function generateInvoice(Tenant $tenant): PlatformInvoice
    {
        $amount = $tenant->monthlyTotal();
        $start  = now()->startOfMonth()->toDateString();
        $end    = now()->endOfMonth()->toDateString();

        $invoice = PlatformInvoice::create([
            'tenant_id'            => $tenant->id,
            'invoice_number'       => PlatformInvoice::generateNumber(),
            'plan'                 => 'modules',
            'amount'               => $amount,
            'status'               => 'unpaid',
            'due_date'             => now()->addDays((int) (BillingSetting::get('invoice_due_days') ?? 7))->toDateString(),
            'billing_period_start' => $start,
            'billing_period_end'   => $end,
        ]);

        $tenant->update(['last_billing_date' => now()]);

        $owner = $tenant->users()->where('role', 'owner')->first();
        if ($owner) {
            $owner->notify(new BillingInvoiceCreatedNotification($invoice));
        }

        return $invoice;
    }

    public function runDailyCheck(): void
    {
        // 1. Mark unpaid -> overdue where due_date < today
        PlatformInvoice::where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        // 2. For newly overdue (no grace set) — start grace period
        $newlyOverdue = PlatformInvoice::where('status', 'overdue')
            ->whereHas('tenant', fn ($q) => $q->whereNull('grace_period_ends_at')->whereNull('suspended_at'))
            ->with('tenant.users')
            ->get();

        foreach ($newlyOverdue as $invoice) {
            $tenant = $invoice->tenant;
            $tenant->update(['grace_period_ends_at' => now()->addDays((int) (BillingSetting::get('grace_period_days') ?? 5))]);
            $owner = $tenant->users()->where('role', 'owner')->first();
            if ($owner) {
                $owner->notify(new BillingGracePeriodStartedNotification($tenant));
            }
        }

        // 3. Grace period expiry warnings (2 days left and 1 day left)
        $inGrace = Tenant::whereNotNull('grace_period_ends_at')
            ->whereNull('suspended_at')
            ->get();

        foreach ($inGrace as $tenant) {
            $daysLeft = $tenant->graceDaysLeft();
            if (!in_array($daysLeft, [2, 1])) continue;

            // Only send once per day to prevent duplicate warnings from re-runs
            if ($tenant->last_grace_warning_sent_at?->isToday()) continue;

            $owner = $tenant->users()->where('role', 'owner')->first();
            if ($owner) {
                $owner->notify(new BillingGracePeriodExpiringNotification($tenant, $daysLeft));
                $tenant->update(['last_grace_warning_sent_at' => now()]);
            }
        }

        // 4. Suspend accounts whose grace period has expired
        $expired = Tenant::whereNotNull('grace_period_ends_at')
            ->whereNull('suspended_at')
            ->where('grace_period_ends_at', '<=', now())
            ->get();

        foreach ($expired as $tenant) {
            $tenant->update([
                'suspended_at'        => now(),
                'grace_period_ends_at'=> null,
            ]);
            $owner = $tenant->users()->where('role', 'owner')->first();
            if ($owner) {
                $owner->notify(new BillingServiceSuspendedNotification());
            }
        }
    }

    public function tenantsDueForBilling(): Collection
    {
        $startOfMonth = now()->startOfMonth()->toDateString();

        return Tenant::where('is_active', true)
            ->whereNull('suspended_at')
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                  ->orWhere('trial_ends_at', '<', now());
            })
            ->whereDoesntHave('platformInvoices', function ($q) use ($startOfMonth) {
                $q->where('billing_period_start', $startOfMonth);
            })
            ->get();
    }

    public function recordPayment(PlatformInvoice $invoice, string $method, string $reference): void
    {
        $wasSuspended = (bool) $invoice->tenant->suspended_at;

        $invoice->update([
            'status'            => 'paid',
            'paid_at'           => now(),
            'payment_method'    => $method,
            'payment_reference' => $reference,
        ]);

        $invoice->tenant->update([
            'suspended_at'        => null,
            'grace_period_ends_at'=> null,
        ]);

        if ($wasSuspended) {
            $owner = $invoice->tenant->users()->where('role', 'owner')->first();
            if ($owner) {
                $owner->notify(new BillingServiceReactivatedNotification());
            }
        }
    }
}
