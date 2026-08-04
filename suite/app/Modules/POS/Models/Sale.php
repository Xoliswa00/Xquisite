<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Sale extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'reference',
        'idempotency_key',
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
        DB::transaction(function () {
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
        });
    }

    /**
     * Temporary, always-unique placeholder for the initial insert (satisfies
     * the unique `reference` column without a max(id)+1 race). Call
     * assignSequentialReference() immediately after create() to swap in the
     * real SAL-00042 form once the row's own id exists.
     */
    public static function generateReference(): string
    {
        return 'SAL-PENDING-' . (string) Str::uuid();
    }

    public function assignSequentialReference(): void
    {
        $this->update(['reference' => 'SAL-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT)]);
    }
}
