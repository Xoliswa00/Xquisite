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

class Contractor extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasTenant, Authenticatable, Notifiable, Auditable, CanResetPassword;

    protected $fillable = [
        'tenant_id', 'name', 'company_name', 'trade', 'email', 'phone',
        'is_active', 'notes', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('contractor.password.reset', ['slug' => $this->tenant->slug, 'token' => $token])
            . '?email=' . urlencode($this->email);

        $this->notify(new PortalResetPasswordNotification($url, 'Contractor Portal', $this->tenant->name));
    }

    /** Jobs this contractor has won — their approved quote made them the assigned contractor. */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /** Jobs this contractor was invited to quote on, whether or not they won it. */
    public function invitedJobs()
    {
        return $this->belongsToMany(MaintenanceRequest::class, 'maintenance_request_contractor')->withTimestamps();
    }

    public function quotes()
    {
        return $this->hasMany(MaintenanceQuote::class);
    }
}
