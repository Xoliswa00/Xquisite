<?php

namespace App\Modules\Booking\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use App\Notifications\PortalResetPasswordNotification;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Notifications\Notifiable;

class Customer extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasTenant, Auditable, Authenticatable, Notifiable, SoftDeletes, CanResetPassword;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'notes',
        'is_active',
        'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('book.password.reset', ['slug' => $this->tenant->slug, 'token' => $token])
            . '?email=' . urlencode($this->email);

        $this->notify(new PortalResetPasswordNotification($url, 'Customer Portal', $this->tenant->name));
    }
}
