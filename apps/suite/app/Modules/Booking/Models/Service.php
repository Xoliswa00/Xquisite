<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;


class Service extends Model
{

    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_active',
    ];

    public function serviceProducts()
    {
        return $this->hasMany(ServiceProduct::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_services');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
