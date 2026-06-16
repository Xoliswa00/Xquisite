<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class StockAdjustment extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'sale_id',
        'purchase_order_id',
        'reference',
        'notes',
        'created_by',
    ];

    const TYPE_SALE         = 'sale';
    const TYPE_STOCKTAKE    = 'stocktake';
    const TYPE_RECEIVE      = 'receive';
    const TYPE_MANUAL_IN    = 'adjustment_in';
    const TYPE_MANUAL_OUT   = 'adjustment_out';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function getLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SALE       => 'Sale',
            self::TYPE_STOCKTAKE  => 'Stock Take',
            self::TYPE_RECEIVE    => 'PO Received',
            self::TYPE_MANUAL_IN  => 'Manual In',
            self::TYPE_MANUAL_OUT => 'Manual Out',
            default               => ucfirst($this->type),
        };
    }
}
