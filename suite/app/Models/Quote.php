<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasTenant;
    protected $fillable = [
        'tenant_id', 'customer_id', 'created_by', 'reference', 'title',
        'line_items', 'subtotal', 'tax_rate', 'tax_amount', 'total',
        'deposit_percentage', 'status', 'valid_until', 'notes',
        'client_email', 'payment_plan_id', 'converted_to_appointment_id',
    ];

    protected $casts = [
        'line_items'         => 'array',
        'subtotal'           => 'decimal:2',
        'tax_rate'           => 'decimal:2',
        'tax_amount'         => 'decimal:2',
        'total'              => 'decimal:2',
        'deposit_percentage' => 'decimal:2',
        'valid_until'        => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Quote $quote) {
            if (! $quote->reference) {
                $quote->reference = 'QT-' . strtoupper(Str::random(6));
            }
        });
    }

    public function tenant()      { return $this->belongsTo(Tenant::class); }
    public function customer()    { return $this->belongsTo(\App\Modules\Booking\Models\Customer::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function paymentPlan() { return $this->belongsTo(PaymentPlan::class); }
    public function appointment() { return $this->belongsTo(\App\Modules\Booking\Models\Appointment::class, 'converted_to_appointment_id'); }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['draft', 'sent', 'accepted']);
    }

    public function depositAmount(): float
    {
        return round($this->total * ($this->deposit_percentage / 100), 2);
    }

    public function recalculate(): void
    {
        $subtotal = collect($this->line_items)->sum('total');
        $taxAmount = round($subtotal * ($this->tax_rate / 100), 2);
        $this->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => $subtotal + $taxAmount,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status === 'sent';
    }

    public function acceptToken(): string
    {
        return hash_hmac('sha256', $this->id . $this->reference, config('app.key'));
    }
}
