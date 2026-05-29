<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'custom_domain_verified',
        'email',
        'phone',
        'plan',
        'industry',
        'logo_url',
        'is_active',
        'trial_ends_at',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'custom_domain_verified' => 'boolean',
        'trial_ends_at'          => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tenantModules()
    {
        return $this->hasMany(TenantModule::class);
    }

    public function activeModules()
    {
        return $this->hasMany(TenantModule::class)->where('is_active', true);
    }

    // ── Module helpers ─────────────────────────────────────────

    public function hasModule(string $module): bool
    {
        return $this->activeModules->contains('module', $module);
    }

    public function activateModule(string $module, ?int $activatedBy = null, ?float $priceOverride = null, ?int $billingSubscriptionId = null): TenantModule
    {
        return $this->tenantModules()->updateOrCreate(
            ['module' => $module],
            [
                'is_active'               => true,
                'price_override'          => $priceOverride,
                'activated_at'            => now(),
                'activated_by'            => $activatedBy,
                'deactivated_at'          => null,
                'billing_subscription_id' => $billingSubscriptionId,
            ]
        );
    }

    public function deactivateModule(string $module): void
    {
        $this->tenantModules()
            ->where('module', $module)
            ->update(['is_active' => false, 'deactivated_at' => now()]);
    }

    public function monthlyTotal(): float
    {
        return $this->activeModules->sum(fn (TenantModule $tm) => $tm->monthly_price);
    }

    // ── Other helpers ──────────────────────────────────────────

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getStorefrontUrlAttribute(): string
    {
        if ($this->custom_domain && $this->custom_domain_verified) {
            return 'https://' . $this->custom_domain;
        }

        if ($this->subdomain) {
            return 'https://' . $this->subdomain . '.' . config('app.domain', 'xquisite.co.za');
        }

        return route('shop.index', $this->slug);
    }
}
