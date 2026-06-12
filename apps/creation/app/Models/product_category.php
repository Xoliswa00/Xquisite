<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_category extends Model
{
    /** @use HasFactory<\Database\Factories\ProductCategoryFactory> */
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
        'description',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);    }
    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');}


        public function group()
{
    return $this->belongsTo(Product_Group::class);
}


}
