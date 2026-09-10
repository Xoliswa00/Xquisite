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
        $start  = now()->startOfMonth()->toDateString();
        $end    = now()->endOfMonth()->toDateString();

        if ($tenant->platformInvoices()->where('billing_period_start', $start)->exists()) {
            throw new \RuntimeException("Tenant #{$tenant->id} already has an invoice for the billing period starting {$start}.");
        }

        $lineItems = $tenant->monthlyLineItems();
        $amount    = $tenant->monthlyTotal();

        // The snapshot must reconcile with what's charged, always — this is the
        // guarantee the frozen line_items exist to give.
        $lineTotal = round(array_sum(array_column($lineItems, 'amount')), 2);
        if (abs($lineTotal - round($amount, 2)) > 0.01) {
            throw new \RuntimeException(
                "Invoice line items (R{$lineTotal}) do not reconcile with the billed amount (R" . round($amount, 2) . ") for tenant #{$tenant->id}."
            );
        }

        // VAT is inclusive: the module prices already contain it, so it is backed
        // out of the total rather than added on top. vat_rate 0 (the default)
        // means no VAT — the document then reads "Invoice", not "Tax Invoice".
        $vatRate   = (float) (BillingSetting::get('vat_rate') ?? 0);
        $vatAmount = $vatRate > 0 ? round($amount * $vatRate / (100 + $vatRate), 2) : 0.0;
        $subtotal  = round($amount - $vatAmount, 2);

        $invoice = PlatformInvoice::create([
            'tenant_id'            => $tenant->id,
            'invoice_number'       => PlatformInvoice::generateNumber(),
            'plan'                 => 'modules',
            'amount'               => $amount,
            'line_items'           => $lineItems,
            'subtotal'             => $subtotal,
            'vat_amount'           => $vatAmount,
            'discount_amount'      => 0,
            'status'               => 'unpaid',
            'due_date'             => now()->addDays((int) (BillingSetting::get('invoice_due_days') ?? 7))->toDateString(),
            'billing_period_start' => $start,
            'billing_period_end'   => $end,
        ]);

        $tenant->update(['last_billing_date' => now()]);

        $owner = $tenant->owner();
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
            $owner = $tenant->owner();
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

            $owner = $tenant->owner();
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
            $owner = $tenant->owner();
            if ($owner) {
                $owner->notify(new BillingServiceSuspendedNotification());
            }
        }
    }

    public function tenantsDueForBilling(): Collection
    {
        $startOfMonth = now()->startOfMonth()->toDateString();

        return Tenant::where('is_active', true)
            ->where('is_demo', false)
            ->whereNull('suspended_at')
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                  ->orWhere('trial_ends_at', '<', now());
            })
            ->whereHas('activeModules')
            ->whereDoesntHave('platformInvoices', function ($q) use ($startOfMonth) {
                $q->where('billing_period_start', $startOfMonth);
            })
            ->with('activeModules.platformModule')
            ->get();
    }

    /**
     * Active tenants past trial with no active module — would previously have
     * been billed a flat/incorrect amount by mistake; now excluded from billing
     * entirely. Surfaced here so admins notice them instead of them going silent.
     */
    public function tenantsActiveWithNoModules(): Collection
    {
        return Tenant::where('is_active', true)
            ->where('is_demo', false)
            ->whereNull('suspended_at')
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                  ->orWhere('trial_ends_at', '<', now());
            })
            ->whereDoesntHave('activeModules')
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
            $owner = $invoice->tenant->owner();
            if ($owner) {
                $owner->notify(new BillingServiceReactivatedNotification());
            }
        }
    }
}
