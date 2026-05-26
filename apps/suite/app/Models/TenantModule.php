<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    protected $fillable = [
        'tenant_id',
        'module',
        'is_active',
        'price_override',
        'activated_at',
        'deactivated_at',
        'activated_by',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'price_override'   => 'decimal:2',
        'activated_at'     => 'datetime',
        'deactivated_at'   => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function activatedBy()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function getMonthlyPriceAttribute(): float
    {
        if ($this->price_override !== null) {
            return (float) $this->price_override;
        }

        return (float) (config("modules.{$this->module}.price") ?? 0);
    }

    public function getDisplayNameAttribute(): string
    {
        return config("modules.{$this->module}.name") ?? ucfirst($this->module);
    }
}
