<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'customer_id', 'name', 'email', 'phone', 'notes',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Modules\Booking\Models\Customer::class);
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }
}
