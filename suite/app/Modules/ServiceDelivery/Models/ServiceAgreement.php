<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ServiceAgreement extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'client_id', 'sla_plan_id', 'service_type', 'plan_name',
        'monthly_fee', 'minutes_allowance', 'start_date', 'commitment_months',
        'billing_day', 'status', 'late_stage', 'last_reminder_stage_sent',
        'suspended_at', 'terminated_at', 'termination_reason', 'reactivation_fee',
        'accepted_at', 'accepted_name', 'notes', 'billing_subscription_id',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'suspended_at'   => 'datetime',
        'terminated_at'  => 'date',
        'accepted_at'    => 'date',
        'monthly_fee'    => 'decimal:2',
        'reactivation_fee' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function slaPlan()
    {
        return $this->belongsTo(SlaPlan::class);
    }

    public function charges()
    {
        return $this->hasMany(ServiceAgreementCharge::class);
    }

    public function changes()
    {
        return $this->hasMany(ServiceAgreementChange::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public static function currentPeriod(): string
    {
        return now()->format('Y-m');
    }

    public function commitmentEndDate(): Carbon
    {
        return Carbon::parse($this->start_date)->addMonths($this->commitment_months);
    }

    /** Generate the charge record for the current month if not yet created. */
    public function generateCurrentPeriodCharge(): ServiceAgreementCharge
    {
        $period = static::currentPeriod();

        return ServiceAgreementCharge::firstOrCreate(
            ['service_agreement_id' => $this->id, 'period' => $period],
            [
                'tenant_id'  => $this->tenant_id,
                'client_id'  => $this->client_id,
                'amount_due' => $this->monthly_fee,
                'amount_paid' => 0,
                'status'     => 'pending',
                'due_date'   => Carbon::parse($period . '-01')->addDays($this->billing_day - 1)->toDateString(),
            ]
        );
    }

    public function minutesUsedThisPeriod(): int
    {
        return (int) $this->changes()->where('period', static::currentPeriod())->sum('minutes_used');
    }

    public function minutesRemaining(): int
    {
        return max(0, $this->minutes_allowance - $this->minutesUsedThisPeriod());
    }
}
