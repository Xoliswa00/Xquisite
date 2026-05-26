<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'pricing_type',
        'price',
        'min_price',
        'max_price',
        'vat_rate',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'effective_from' => 'datetime',
        'effective_to'   => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
