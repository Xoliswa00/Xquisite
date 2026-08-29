<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class LeaseCharge extends Model
{
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'lease_id', 'rent_payment_id', 'type', 'description', 'period',
        'amount_excl', 'vat_rate', 'vat_amount', 'amount_incl', 'amount_paid',
        'status', 'due_date', 'paid_date', 'payment_method', 'reference',
        'meter_reading_start', 'meter_reading_end', 'rate_per_unit', 'notes',
    ];

    protected $casts = [
        'due_date'             => 'date',
        'paid_date'            => 'date',
        'amount_excl'          => 'decimal:2',
        'vat_rate'             => 'decimal:2',
        'vat_amount'           => 'decimal:2',
        'amount_incl'          => 'decimal:2',
        'amount_paid'          => 'decimal:2',
        'meter_reading_start'  => 'decimal:2',
        'meter_reading_end'    => 'decimal:2',
        'rate_per_unit'        => 'decimal:4',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function rentPayment()
    {
        return $this->belongsTo(RentPayment::class);
    }

    public function outstanding(): float
    {
        return (float) $this->amount_incl - (float) $this->amount_paid;
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['pending', 'partial']) && $this->due_date && $this->due_date->isPast();
    }
}
