<?php

namespace App\Modules\Ecommerce\Models;

use App\Modules\POS\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Auditable;

class OrderItem extends Model
{
    use Auditable;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_image_url',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
