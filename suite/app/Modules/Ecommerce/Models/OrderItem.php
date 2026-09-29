<?php

namespace App\Modules\Ecommerce\Models;

use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Auditable;

class OrderItem extends Model
{
    use Auditable;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'variant_attributes',
        'product_name',
        'product_sku',
        'product_image_url',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price'         => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'variant_attributes' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
