<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'is_active',
        'require_password_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'require_password_change' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Role helpers ───────────────────────────────────────────
    // Spatie is the single source of truth. These are thin, readable wrappers
    // over hasRole() — the legacy `users.role` column no longer exists.

    public function isOwner(): bool
    {
        return $this->hasRole('tenant-owner');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'tenant-owner', 'manager']);
    }

    public function isStaff(): bool
    {
        return $this->hasRole('employee');
    }

    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    public function isPlatformOwner(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isClientUser(): bool
    {
        return $this->isClient();
    }

    public function isSystemAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function client()
    {
        return $this->hasOne(\App\Models\Client::class);
    }

    public function needsPasswordChange(): bool
    {
        return $this->require_password_change === true;
    }

    public function markPasswordChanged(): void
    {
        $this->update([
            'require_password_change' => false,
            'password_changed_at' => now(),
        ]);
    }
}
