<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class product_items extends Model
{
    //
    protected $fillable = [
        'product_id',
        'name',
        
        'description',
        'sort_order',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    

}
