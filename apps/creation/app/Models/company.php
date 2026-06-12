<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class company extends Model
{
    use SoftDeletes;
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country',
        'email',
        'phone',
        'website',
        'entity_type',
        'legal_name',
        'registration_number',
        'tax_number',
        'vat_number',
        'currency',
        'vat_registered',
        'default_vat_rate',
        'status',
        'slug',
        'domain',
        'logo_path',
        'timezone',
        'billing_email',
        'plan',
        'subscription_plan',
        'subscription_status',
        'subscription_renewal_date',
        'grace_period_ends_at',
        'suspended_at',
        'last_billing_date',
        'platform_owner_id',
        'is_platform_company'
    ];

    protected $casts = [
        'grace_period_ends_at'      => 'datetime',
        'suspended_at'              => 'datetime',
        'last_billing_date'         => 'datetime',
        'subscription_renewal_date' => 'date',
        'vat_registered'            => 'boolean',
        'is_platform_company'       => 'boolean',
    ];

     public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'platform_owner_id');
    }

    public function invitations()
    {
        return $this->hasMany(Company_invitations::class);
    }

    public function domains()
    {
        return $this->hasMany(Company_domains::class);
    }
    public function clients()
    {
        return $this->hasMany(Client::class, 'company_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    public function platformInvoices()
    {
        return $this->hasMany(PlatformInvoice::class, 'company_id');
    }

    public function unpaidPlatformInvoices()
    {
        return $this->platformInvoices()->whereIn('status', ['unpaid', 'overdue']);
    }

    // --- Billing helpers ---

    public static function planAmount(string $plan): float
    {
        return match ($plan) {
            'premium'    => 599.00,
            'enterprise' => 1299.00,
            default      => 299.00, // basic
        };
    }

    public function isInGrace(): bool
    {
        return $this->grace_period_ends_at !== null
            && $this->grace_period_ends_at->isFuture()
            && $this->status === 'active';
    }

    public function graceDaysLeft(): int
    {
        if (!$this->isInGrace()) return 0;
        return (int) now()->diffInDays($this->grace_period_ends_at, false);
    }

    public function billingStatusLabel(): string
    {
        if ($this->status === 'suspended') return 'Suspended';
        if ($this->isInGrace()) return 'Grace Period';
        if ($this->unpaidPlatformInvoices()->exists()) return 'Payment Due';
        return 'Active';
    }

    public function billingStatusClass(): string
    {
        return match ($this->billingStatusLabel()) {
            'Suspended'    => 'bg-rose-100 text-rose-700 border-rose-200',
            'Grace Period' => 'bg-amber-100 text-amber-700 border-amber-200',
            'Payment Due'  => 'bg-orange-100 text-orange-700 border-orange-200',
            default        => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        };
    }

}
