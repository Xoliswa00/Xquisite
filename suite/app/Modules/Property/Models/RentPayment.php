<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class RentPayment extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'lease_id', 'renter_id', 'unit_id', 'period',
        'amount_due', 'amount_paid', 'status', 'due_date', 'paid_date',
        'payment_method', 'reference', 'notes',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'paid_date'   => 'date',
        'amount_due'  => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function renter()
    {
        return $this->belongsTo(Renter::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function periodLabel(): string
    {
        return \Carbon\Carbon::parse($this->period . '-01')->format('F Y');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }
}
