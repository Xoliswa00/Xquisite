<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class SlaPlan extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'key', 'name', 'monthly_fee', 'minutes_allowance',
        'features', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'features'    => 'array',
        'is_active'   => 'boolean',
    ];

    public function serviceAgreements()
    {
        return $this->hasMany(ServiceAgreement::class);
    }

    /** Seed the four standard tiers for a tenant the first time they activate the module. */
    public static function seedDefaultsFor(int $tenantId): void
    {
        if (static::where('tenant_id', $tenantId)->exists()) {
            return;
        }

        $defaults = [
            [
                'key' => 'foundation', 'name' => 'Foundation', 'monthly_fee' => 850.00,
                'minutes_allowance' => 60, 'sort_order' => 1,
                'features' => ['Website updates', 'Security updates', 'Backups', 'Email support', 'Minor fixes'],
            ],
            [
                'key' => 'business', 'name' => 'Business', 'monthly_fee' => 2500.00,
                'minutes_allowance' => 180, 'sort_order' => 2,
                'features' => [
                    'Everything in Foundation', 'Analytics support', 'Basic automation support',
                    'Monthly system checks', 'Priority support',
                ],
            ],
            [
                'key' => 'professional', 'name' => 'Professional', 'monthly_fee' => 5000.00,
                'minutes_allowance' => 360, 'sort_order' => 3,
                'features' => [
                    'Everything in Business', 'Database maintenance', 'Performance optimisation',
                    'Dashboard changes', 'Quarterly review',
                ],
            ],
            [
                'key' => 'enterprise', 'name' => 'Enterprise', 'monthly_fee' => 10000.00,
                'minutes_allowance' => 600, 'sort_order' => 4,
                'features' => [
                    'Dedicated support', 'Multiple systems covered', 'Strategic reviews',
                    'Priority response', 'Advanced monitoring',
                ],
            ],
        ];

        foreach ($defaults as $plan) {
            static::create(array_merge($plan, ['tenant_id' => $tenantId, 'is_active' => true]));
        }
    }
}
