<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class quote extends Model
{
    /** @use HasFactory<\Database\Factories\QuoteFactory> */
    use HasFactory;
    protected $fillable = [
        'company_id',
        'client_id',
        'quote_number',
        'status',
        'total',
        'status',
        'source',
        'internal_note',
        'client_note',
        'subtotal',
        'vat',
        'created_by',
        

    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function items()
    {
        return $this->hasMany(Quote_items::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
}
