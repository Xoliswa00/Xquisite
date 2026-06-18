<?php

namespace App\Models;

use App\Models\Tenant;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RentalOrder extends Model
{
    use HasTenant;
    protected $fillable = [
        'tenant_id', 'product_id', 'appointment_id', 'customer_id',
        'reference', 'quantity', 'rental_rate', 'event_date',
        'return_due_at', 'returned_at', 'condition_on_return',
        'status', 'notes',
    ];

    protected $casts = [
        'rental_rate'   => 'decimal:2',
        'event_date'    => 'date',
        'return_due_at' => 'date',
        'returned_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (RentalOrder $order) {
            if (! $order->reference) {
                $order->reference = 'RNT-' . strtoupper(Str::random(6));
            }
        });
    }

    public function product()     { return $this->belongsTo(\App\Modules\POS\Models\Product::class); }
    public function appointment() { return $this->belongsTo(\App\Modules\Booking\Models\Appointment::class); }
    public function customer()    { return $this->belongsTo(\App\Modules\Booking\Models\Customer::class); }
    public function tenant()      { return $this->belongsTo(Tenant::class); }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['reserved', 'out']);
    }

    public function scopeOverdue(Builder $q): Builder
    {
        return $q->where('status', 'out')
                 ->where('return_due_at', '<', now()->toDateString());
    }

    public function totalCharge(): float
    {
        return round($this->rental_rate * $this->quantity, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'out' && $this->return_due_at->isPast();
    }

    public function markReturned(string $condition): void
    {
        $status = in_array($condition, ['excellent', 'good', 'fair']) ? 'returned' : 'damaged';

        $this->update([
            'returned_at'        => now(),
            'condition_on_return' => $condition,
            'status'             => $status,
        ]);
    }
}
