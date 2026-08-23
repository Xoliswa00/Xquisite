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

    /**
     * db_connection, queue_status, and version are reported inside the
     * metadata JSON blob (see HealthReportController@store), not as real
     * columns. These accessors let the monitoring views read them as plain
     * properties instead of silently resolving to null.
     */
    public function getDbConnectionAttribute()
    {
        return $this->metadata['db_connection'] ?? null;
    }

    public function getQueueStatusAttribute()
    {
        return $this->metadata['queue_status'] ?? null;
    }

    public function getVersionAttribute()
    {
        return $this->metadata['version'] ?? null;
    }

    /**
     * Accessor for ->last_error, used in the monitoring views — the real
     * column is error_message.
     */
    public function getLastErrorAttribute()
    {
        return $this->error_message;
    }
}
