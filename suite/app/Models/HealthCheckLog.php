<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthCheckLog extends Model
{
    protected $fillable = [
        'monitored_instance_id',
        'status',
        'response_time_ms',
        'error_message',
        'metadata',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(MonitoredInstance::class, 'monitored_instance_id');
    }

    public function isSuccess(): bool
    {
        return $this->status === 'up' && !$this->error_message;
    }
}
