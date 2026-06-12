<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_company_id', // added to allow mass assignment
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Many-to-Many relationship to companies.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Current active company of the user.
     */
    public function currentCompany()
    {
        return $this->belongsTo(Company::class, 'is_platform_owner_id');
    }

    /**
     * User role in the current company.
     */
    public function role()
    {
        // If no current company is set, return null
        if (! $this->currentCompany) {
            return null;
        }

        // Get the company pivot record safely
        $company = $this->companies()
                        ->where('company_id', $this->current_company_id)
                        ->first();

        return $company?->pivot->role; // null safe
    }

    /**
     * Check if user has a specific role in the current company.
     */
    public function hasRole(string $role): bool
    {
        return $this->role() === $role;
    }

    /**
     * Check if user is admin in the current company.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    public function isPlatformOwner(): bool
    {
        return $this->role === 'platform_owner';
    }

    public function isSystemAdmin(): bool
    {
        return $this->role === 'system_admin';
    }

    public function isClientUser(): bool
    {
        return $this->role === 'client_user';
    }

    // Users a platform owner has created
    public function managedCompanies()
    {
        return $this->hasMany(Company::class, 'platform_owner_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Return the current company model instance based on current_company_id.
     * This ensures auth()->user()->currentCompany->quotes() works even if the
     * currentCompany() relation was misconfigured elsewhere.
     */
    public function getCurrentCompanyAttribute()
    {
        if (! $this->current_company_id) {
            return null;
        }

        return $this->companies()
                    ->where('companies.id', $this->current_company_id)
                    ->first();
    }

    /**
     * Expose role as a property so checks like $this->role === 'admin' work.
     */
    public function getRoleAttribute()
    {
        return $this->role();
    }
    
}