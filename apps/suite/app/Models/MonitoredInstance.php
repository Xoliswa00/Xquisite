<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoredInstance extends Model
{
    protected $fillable = [
        'name',
        'url',
        'api_token',
        'tenant_id',
        'status',
        'uptime_percentage',
        'last_check_at',
        'last_error_at',
        'last_error_message',
        'consecutive_failures',
        'is_active',
    ];

    protected $casts = [
        'last_check_at' => 'datetime',
        'last_error_at' => 'datetime',
        'is_active' => 'boolean',
        'uptime_percentage' => 'decimal:2',
    ];

    protected $hidden = ['api_token'];

    public function healthChecks(): HasMany
    {
        return $this->hasMany(HealthCheckLog::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(InstanceAlert::class);
    }

    public function activeAlerts(): HasMany
    {
        return $this->alerts()->where('is_resolved', false);
    }

    public function isHealthy(): bool
    {
        return $this->status === 'up' && $this->consecutive_failures === 0;
    }

    public function getUptimeClass(): string
    {
        if ($this->uptime_percentage >= 99.5) {
            return 'excellent';
        } elseif ($this->uptime_percentage >= 99) {
            return 'good';
        } elseif ($this->uptime_percentage >= 98) {
            return 'fair';
        }
        return 'poor';
    }
}
