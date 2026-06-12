<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_group extends Model
{
    /** @use HasFactory<\Database\Factories\ProductGroupFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
    ];
    public function products()
    {
        return $this->hasMany(Product::class, 'product_group_id');
    }
    public function categories()
    {
        return $this->hasMany(product_category::class, 'product_group_id');
    }
}
