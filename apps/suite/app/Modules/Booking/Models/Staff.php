<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;


class Staff extends Model
{
    //
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'role',
        'is_active',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'staff_services');
    }

    public function schedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function blocks()
    {
        return $this->hasMany(StaffBlock::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
