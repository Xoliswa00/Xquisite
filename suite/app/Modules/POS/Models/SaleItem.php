<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Auditable;

class SaleItem extends Model
{
    use Auditable;

    protected $fillable = [
        'sale_id',
        'item_type',
        'item_id',
        'product_variant_id',
        'variant_attributes',
        'name',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price'         => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'variant_attributes' => 'array',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
