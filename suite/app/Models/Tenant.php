<?php

namespace App\Models;

use App\Models\ModuleRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'custom_domain_verified',
        'email',
        'phone',
        'address',
        'bank_name',
        'bank_account_type',
        'bank_account_holder',
        'bank_account_number',
        'bank_branch_code',
        'vat_number',
        'plan',
        'industry',
        'preferred_template_key',
        'logo_url',
        'shipping_enabled',
        'shipping_type',
        'shipping_cost',
        'is_active',
        'is_demo',
        'trial_ends_at',
        'grace_period_ends_at',
        'last_grace_warning_sent_at',
        'suspended_at',
        'last_billing_date',
    ];

    protected $casts = [
        'shipping_enabled'           => 'boolean',
        'shipping_cost'              => 'decimal:2',
        'is_active'                  => 'boolean',
        'is_demo'                    => 'boolean',
        'custom_domain_verified'     => 'boolean',
        'trial_ends_at'              => 'datetime',
        'grace_period_ends_at'       => 'datetime',
        'last_grace_warning_sent_at' => 'datetime',
        'suspended_at'               => 'datetime',
        'last_billing_date'          => 'datetime',
    ];

    // ── Mutators ────────────────────────────────────────────────

    public function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = $value !== null ? strtolower(trim($value)) : $value;
    }

    public function setSubdomainAttribute($value): void
    {
        $this->attributes['subdomain'] = $value !== null && $value !== '' ? strtolower(trim($value)) : null;
    }

    // ── Relationships ──────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /** The tenant's owner user (spatie 'tenant-owner' role). */
    public function owner(): ?User
    {
        return $this->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'tenant-owner'))
            ->first();
    }

    public function tenantModules()
    {
        return $this->hasMany(TenantModule::class);
    }

    public function activeModules()
    {
        return $this->hasMany(TenantModule::class)->where('is_active', true);
    }

    public function moduleRequests()
    {
        return $this->hasMany(ModuleRequest::class);
    }

    public function pendingModuleRequests()
    {
        return $this->moduleRequests()->where('status', 'pending');
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
        $modules = $this->relationLoaded('activeModules')
            ? $this->activeModules
            : $this->activeModules()->with('platformModule')->get();

        return $modules->sum(fn (TenantModule $tm) => $tm->monthly_price);
    }

    public function platformInvoices()
    {
        return $this->hasMany(PlatformInvoice::class);
    }

    public function unpaidPlatformInvoices()
    {
        return $this->platformInvoices()->whereIn('status', ['unpaid', 'overdue']);
    }

    // ── Billing helpers ────────────────────────────────────────

    public function isInGrace(): bool
    {
        return $this->grace_period_ends_at && now()->lt($this->grace_period_ends_at) && !$this->suspended_at;
    }

    public function graceDaysLeft(): int
    {
        if (!$this->grace_period_ends_at) return 0;
        return max(0, (int) now()->diffInDays($this->grace_period_ends_at, false));
    }

    public function billingStatusLabel(): string
    {
        if ($this->suspended_at) return 'Suspended';
        if ($this->isInGrace()) return 'Grace Period';
        $hasUnpaid = $this->relationLoaded('platformInvoices')
            ? $this->platformInvoices->whereIn('status', ['unpaid', 'overdue'])->isNotEmpty()
            : $this->unpaidPlatformInvoices()->exists();
        if ($hasUnpaid) return 'Overdue';
        return 'Active';
    }

    public function billingStatusClass(): string
    {
        if ($this->suspended_at) return 'bg-red-900/40 text-red-300 border-red-700';
        if ($this->isInGrace()) return 'bg-amber-900/40 text-amber-300 border-amber-700';
        $hasUnpaid = $this->relationLoaded('platformInvoices')
            ? $this->platformInvoices->whereIn('status', ['unpaid', 'overdue'])->isNotEmpty()
            : $this->unpaidPlatformInvoices()->exists();
        if ($hasUnpaid) return 'bg-orange-900/40 text-orange-300 border-orange-700';
        return 'bg-emerald-900/40 text-emerald-300 border-emerald-700';
    }

    // ── Storefront / shipping ──────────────────────────────────

    /**
     * Resolve the shipping cost for an order.
     * Collection is always free (nothing to ship). Delivery costs the
     * configured flat rate, unless shipping is disabled or set to free.
     */
    public function calculateShipping(string $fulfillmentType = 'delivery'): float
    {
        if ($fulfillmentType === 'collection') {
            return 0.0;
        }

        if (! $this->shipping_enabled || $this->shipping_type === 'free') {
            return 0.0;
        }

        return (float) $this->shipping_cost;
    }

    // ── Other helpers ──────────────────────────────────────────

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Real (non-placeholder) website templates are a paid-plan feature — a
     * trial tenant only ever gets the free Coming Soon page. Matches the
     * existing "don't do the paid thing during trial" convention used for
     * module billing (see Settings\ModuleController::store()).
     */
    public function canActivateRealTemplate(): bool
    {
        return ! $this->isOnTrial();
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

    // ── Website templates ──────────────────────────────────────

    public function tenantTemplates()
    {
        return $this->hasMany(TenantTemplate::class);
    }

    public function activeTemplate()
    {
        return $this->hasOne(TenantTemplate::class)->where('is_active', true);
    }

    public function branding()
    {
        return $this->hasOne(TenantBranding::class);
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function activateTemplate(string $templateKey, ?int $activatedBy = null): TenantTemplate
    {
        return DB::transaction(function () use ($templateKey, $activatedBy) {
            $this->tenantTemplates()
                ->where('is_active', true)
                ->update(['is_active' => false, 'deactivated_at' => now()]);

            return $this->tenantTemplates()->updateOrCreate(
                ['template_key' => $templateKey],
                [
                    'is_active'      => true,
                    'activated_at'   => now(),
                    'activated_by'   => $activatedBy,
                    'deactivated_at' => null,
                ]
            );
        });
    }

    public function getWebsiteUrlAttribute(): string
    {
        if ($this->custom_domain && $this->custom_domain_verified) {
            return 'https://' . $this->custom_domain;
        }

        if ($this->subdomain) {
            return 'https://' . $this->subdomain . '.' . config('app.domain', 'xquisite.co.za');
        }

        return route('site.index', $this->slug);
    }
}
