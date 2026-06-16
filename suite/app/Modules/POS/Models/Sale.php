<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;

class Sale extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'reference',
        'appointment_id',
        'customer_id',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'payment_method',
        'notes',
        'paid_at',
        'served_by',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total'           => 'decimal:2',
        'paid_at'         => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentPlan()
    {
        return $this->morphOne(\App\Models\PaymentPlan::class, 'plannable');
    }

    public function isLayby(): bool
    {
        return $this->status === 'layby';
    }

    public function markLaybyComplete(): void
    {
        // Deduct stock for all product items now that layby is fully paid
        foreach ($this->items as $item) {
            if ($item->item_type === 'product') {
                $product = Product::find($item->item_id);
                $product?->decrementStock($item->quantity, 'layby_complete', [
                    'sale_id'   => $this->id,
                    'reference' => $this->reference,
                ]);
            }
        }

        $this->update(['status' => 'paid', 'paid_at' => now()]);
    }

    public static function generateReference(): string
    {
        $last = static::max('id') ?? 0;
        return 'SAL-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
