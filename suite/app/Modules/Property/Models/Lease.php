<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'renter_id',
        'start_date', 'signed_date', 'end_date', 'monthly_rent', 'deposit_amount',
        'deposit_paid', 'status', 'notes',
        'terminated_at', 'termination_reason', 'billing_subscription_id',
        'deposit_status', 'deposit_refund_amount', 'deposit_refund_date', 'deposit_refund_method',
        'notified_thresholds',
    ];

    protected $casts = [
        'start_date'            => 'date',
        'signed_date'            => 'date',
        'end_date'               => 'date',
        'terminated_at'          => 'date',
        'deposit_paid'           => 'boolean',
        'deposit_refund_date'    => 'date',
        'deposit_refund_amount'  => 'decimal:2',
        'notified_thresholds'    => 'array',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function renter()
    {
        return $this->belongsTo(Renter::class);
    }

    public function rentPayments()
    {
        return $this->hasMany(RentPayment::class);
    }

    public function charges()
    {
        return $this->hasMany(LeaseCharge::class);
    }

    public function depositDeductions()
    {
        return $this->hasMany(LeaseDepositDeduction::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    /** Sum of everything still owed across rent payments and lease charges. */
    public function outstandingBalance(): float
    {
        $rentOutstanding = $this->rentPayments()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(amount_due - amount_paid), 0) as total')
            ->value('total');

        $chargesOutstanding = $this->charges()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(amount_incl - amount_paid), 0) as total')
            ->value('total');

        return (float) $rentOutstanding + (float) $chargesOutstanding;
    }

    /** Deposit still available to refund, after deductions and any other outstanding balance. Never negative. */
    public function availableDepositRefund(): float
    {
        $deductions = (float) $this->depositDeductions()->sum('amount');

        return max(0, (float) $this->deposit_amount - $deductions - $this->outstandingBalance());
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Generate the period string for the current month */
    public static function currentPeriod(): string
    {
        return now()->format('Y-m');
    }

    /** Generate rent payment records for the current month if not yet created */
    public function generateCurrentPeriodPayment(): RentPayment
    {
        $period = static::currentPeriod();

        return RentPayment::firstOrCreate(
            ['lease_id' => $this->id, 'period' => $period],
            [
                'tenant_id'   => $this->tenant_id,
                'renter_id'   => $this->renter_id,
                'unit_id'     => $this->unit_id,
                'amount_due'  => $this->monthly_rent,
                'amount_paid' => 0,
                'status'      => 'pending',
                'due_date'    => Carbon::parse($period . '-01')->addDays(4)->toDateString(),
            ]
        );
    }
}
