<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;

class Sale extends Model
{
    use HasTenant, Auditable;

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

    /**
     * date + short random suffix, retried on collision — not the old
     * max(id)+1 (Sale uses HasTenant, so max('id') is tenant-scoped, but
     * `reference` has a *global* unique constraint: every tenant's own
     * first sale computed max('id')=null and generated the same
     * 'SAL-00001', so any tenant's Nth sale collided with whichever
     * *other* tenant already had N sales — a guaranteed collision, not a
     * rare race), and not a full ULID either (correct, but 26 characters
     * is rough on a printed till receipt a customer might glance at).
     * withoutGlobalScopes() on the uniqueness check is required, not
     * optional — the constraint is global, checking only this tenant's
     * own rows would silently reintroduce the exact bug this replaced.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'SAL-' . now()->format('ymd') . '-' . static::randomSuffix();
        } while (static::withoutGlobalScopes()->where('reference', $reference)->exists());

        return $reference;
    }

    /** Excludes 0/O and 1/I/L — a receipt reference a customer reads aloud or a cashier retypes into search shouldn't hinge on telling those apart. */
    private static function randomSuffix(int $length = 5): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        return collect(range(1, $length))
            ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
            ->implode('');
    }
}
