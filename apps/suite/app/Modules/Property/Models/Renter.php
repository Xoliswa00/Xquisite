<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Renter extends Model implements AuthenticatableContract
{
    use HasTenant, Authenticatable, Notifiable;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'id_number',
        'emergency_contact_name', 'emergency_contact_phone',
        'notes', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function rentPayments()
    {
        return $this->hasMany(RentPayment::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
