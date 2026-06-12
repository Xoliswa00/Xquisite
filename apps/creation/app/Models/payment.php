<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'company_id',
        'user_id',
        'method',
        'amount',
        'payment_date',
        'reference',
    ];
    protected $casts = [
        'payment_date' => 'date',
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
