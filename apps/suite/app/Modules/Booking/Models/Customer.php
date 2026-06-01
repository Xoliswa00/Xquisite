<?php

namespace App\Modules\Booking\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Notifications\Notifiable;

class Customer extends Model implements AuthenticatableContract
{
    use HasTenant, Auditable, Authenticatable, Notifiable, SoftDeletes;

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
}
