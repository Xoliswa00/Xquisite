<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_price extends Model
{
    /** @use HasFactory<\Database\Factories\ProductPriceFactory> */
    use HasFactory;
    protected $fillable = [
        'product_id',
        'price',
        'billing_type',
        'vat_rate',
        'is_active',

    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    

    
}
