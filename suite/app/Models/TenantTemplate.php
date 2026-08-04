<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantTemplate extends Model
{
    protected $fillable = [
        'tenant_id',
        'template_key',
        'is_active',
        'activated_at',
        'deactivated_at',
        'activated_by',
        'billing_subscription_id',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'activated_at'   => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function activatedBy()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_key', 'key');
    }
}
