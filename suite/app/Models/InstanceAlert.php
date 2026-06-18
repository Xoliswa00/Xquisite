<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstanceAlert extends Model
{
    protected $fillable = [
        'monitored_instance_id',
        'type',
        'title',
        'message',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(MonitoredInstance::class, 'monitored_instance_id');
    }

    public function resolve(): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    public function getSeverityClass(): string
    {
        return match($this->type) {
            'down' => 'critical',
            'error' => 'warning',
            'up' => 'success',
            default => 'info',
        };
    }
}
