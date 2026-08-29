<?php

namespace App\Services\Property;

use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\LeaseCharge;
use App\Modules\Property\Models\RentPayment;
use App\Services\AuditService;

class RentCycleService
{
    /** Applied once per rent payment, the first time it's flagged overdue — not compounded on later runs. */
    private const LATE_FEE_PERCENTAGE = 10;

    /** Generate this month's rent payment for every active lease that doesn't already have one. */
    public function generateMonthly(): int
    {
        $created = 0;

        foreach (Lease::where('status', 'active')->get() as $lease) {
            if ($lease->generateCurrentPeriodPayment()->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Flag pending rent payments and lease charges that are past their due date.
     *
     * Uses mass ->update() for efficiency on what can be a large batch, which bypasses
     * Eloquent events (and therefore Auditable) entirely — so the batch is logged
     * explicitly here instead of relying on per-model event hooks.
     */
    public function flagOverdue(): int
    {
        $overdueRentPayments = RentPayment::where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();
        $rentIds = $overdueRentPayments->pluck('id');
        RentPayment::whereIn('id', $rentIds)->update(['status' => 'overdue']);

        foreach ($overdueRentPayments as $payment) {
            $this->chargeLateFee($payment);
        }

        $chargeIds = LeaseCharge::where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->pluck('id');
        LeaseCharge::whereIn('id', $chargeIds)->update(['status' => 'overdue']);

        if ($rentIds->isNotEmpty()) {
            AuditService::log(
                action: 'rent_payment.bulk_flag_overdue',
                entityType: 'RentPayment',
                meta: ['ids' => $rentIds->all(), 'count' => $rentIds->count()],
            );
        }

        if ($chargeIds->isNotEmpty()) {
            AuditService::log(
                action: 'lease_charge.bulk_flag_overdue',
                entityType: 'LeaseCharge',
                meta: ['ids' => $chargeIds->all(), 'count' => $chargeIds->count()],
            );
        }

        return $rentIds->count() + $chargeIds->count();
    }

    /** Charge a one-off late fee against the lease, linked back to the rent payment so a later run won't duplicate it. */
    private function chargeLateFee(RentPayment $payment): void
    {
        $alreadyCharged = LeaseCharge::where('rent_payment_id', $payment->id)
            ->where('type', 'late_fee')
            ->exists();

        if ($alreadyCharged) {
            return;
        }

        $outstanding = (float) $payment->amount_due - (float) $payment->amount_paid;
        if ($outstanding <= 0) {
            return;
        }

        $amount = round($outstanding * self::LATE_FEE_PERCENTAGE / 100, 2);

        LeaseCharge::create([
            'tenant_id'       => $payment->tenant_id,
            'lease_id'        => $payment->lease_id,
            'rent_payment_id' => $payment->id,
            'type'            => 'late_fee',
            'description'     => 'Late payment fee — ' . $payment->periodLabel(),
            'period'          => $payment->period,
            'amount_excl'     => $amount,
            'vat_rate'        => 0,
            'vat_amount'      => 0,
            'amount_incl'     => $amount,
            'status'          => 'pending',
            'due_date'        => now()->addDays(7),
        ]);
    }
}
