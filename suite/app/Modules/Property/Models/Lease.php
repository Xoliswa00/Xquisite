<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'renter_id',
        'start_date', 'end_date', 'monthly_rent', 'deposit_amount',
        'deposit_paid', 'status', 'notes',
        'terminated_at', 'termination_reason', 'billing_subscription_id',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'terminated_at'  => 'date',
        'deposit_paid'   => 'boolean',
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
