<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class PurchaseOrder extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'reference',
        'supplier_id',
        'supplier',
        'supplier_contact',
        'status',
        'total_cost',
        'notes',
        'created_by',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'total_cost'  => 'decimal:2',
        'sent_at'     => 'datetime',
        'received_at' => 'datetime',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_SENT      = 'sent';
    const STATUS_PARTIAL   = 'partial';
    const STATUS_RECEIVED  = 'received';
    const STATUS_CANCELLED = 'cancelled';

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public static function generateReference(): string
    {
        $last = static::max('id') ?? 0;
        return 'PO-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    public function recalculateTotal(): void
    {
        $this->update([
            'total_cost' => $this->items()->sum(\DB::raw('quantity_ordered * unit_cost')),
        ]);
    }
}
