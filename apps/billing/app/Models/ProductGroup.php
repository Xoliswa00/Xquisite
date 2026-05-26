<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function categories()
    {
        return $this->hasMany(ProductCategory::class, 'product_group_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'product_group_id');
    }
}
