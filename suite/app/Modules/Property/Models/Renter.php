<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use App\Notifications\PortalResetPasswordNotification;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Renter extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasTenant, Authenticatable, Notifiable, Auditable, CanResetPassword;

    protected $fillable = [
        'tenant_id', 'applicant_id', 'name', 'email', 'phone', 'id_number',
        'emergency_contact_name', 'emergency_contact_phone',
        'notes', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('rent.password.reset', ['slug' => $this->tenant->slug, 'token' => $token])
            . '?email=' . urlencode($this->email);

        $this->notify(new PortalResetPasswordNotification($url, 'Renter Portal', $this->tenant->name));
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

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
