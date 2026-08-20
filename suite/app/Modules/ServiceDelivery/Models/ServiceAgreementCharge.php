<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\Client;
use App\Models\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ServiceAgreementCharge extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'service_agreement_id', 'client_id', 'period',
        'amount_due', 'amount_paid', 'status', 'due_date', 'paid_date',
        'payment_method', 'reference', 'notes',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'paid_date'   => 'date',
        'amount_due'  => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function serviceAgreement()
    {
        return $this->belongsTo(ServiceAgreement::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function periodLabel(): string
    {
        return Carbon::parse($this->period . '-01')->format('F Y');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function daysOverdue(): int
    {
        return $this->isOverdue() ? (int) $this->due_date->diffInDays(now()) : 0;
    }
}
