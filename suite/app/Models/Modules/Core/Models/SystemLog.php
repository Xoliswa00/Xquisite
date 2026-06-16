<?php

namespace App\Models\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SystemLog extends Model
{
    protected $table = 'system_logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'file',
        'line',
        'user_id',
        'request_id',
        'ip_address',
        'url',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_note',
        'source',
    ];

    protected $casts = [
        'context'     => 'array',
        'resolved_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function resolvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeUnresolved(Builder $q): Builder
    {
        return $q->whereNotIn('status', ['resolved']);
    }

    public function scopeCritical(Builder $q): Builder
    {
        return $q->whereIn('level', ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY',
                                     'error', 'critical', 'alert', 'emergency']);
    }

    public function scopeNew(Builder $q): Builder
    {
        return $q->where('status', 'new');
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function unresolvedCriticalCount(): int
    {
        return static::new()->critical()->count();
    }

    public function resolve(int $userId, ?string $note = null): void
    {
        $this->update([
            'status'          => 'resolved',
            'resolved_by'     => $userId,
            'resolved_at'     => now(),
            'resolution_note' => $note,
        ]);
    }
}
