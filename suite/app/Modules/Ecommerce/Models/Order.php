<?php

namespace App\Modules\Ecommerce\Models;

use App\Models\Tenant;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'fulfillment_type',
        'shipping_address',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total',
        'notes',
        'payfast_payment_id',
        'paid_at',
        'fulfilled_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'shipping_cost'    => 'decimal:2',
        'total'            => 'decimal:2',
        'paid_at'          => 'datetime',
        'fulfilled_at'     => 'datetime',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_PROCESSING = 'processing';
    const STATUS_READY     = 'ready';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED  = 'refunded';

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function generateReference(): string
    {
        $last = static::max('id') ?? 0;
        return 'ORD-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isActive(): bool
    {
        return !in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_REFUNDED]);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'    => 'Pending Payment',
            'paid'       => 'Paid',
            'processing' => 'Processing',
            'ready'      => 'Ready for Collection',
            'shipped'    => 'Shipped',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            'refunded'   => 'Refunded',
            default      => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'    => 'yellow',
            'paid'       => 'blue',
            'processing' => 'indigo',
            'ready'      => 'purple',
            'shipped'    => 'cyan',
            'delivered'  => 'emerald',
            'cancelled'  => 'red',
            'refunded'   => 'slate',
            default      => 'slate',
        };
    }
}
