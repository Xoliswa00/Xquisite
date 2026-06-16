<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SyncQueue extends Model
{
    protected $table = 'sync_queue';

    protected $fillable = [
        'type',
        'tenant_id',
        'module_key',
        'payload',
        'status',
        'attempts',
        'max_attempts',
        'next_retry_at',
        'last_attempted_at',
        'completed_at',
        'last_error',
        'billing_subscription_id',
    ];

    protected $casts = [
        'payload'            => 'array',
        'next_retry_at'      => 'datetime',
        'last_attempted_at'  => 'datetime',
        'completed_at'       => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeDue(Builder $q): Builder
    {
        return $q->where('status', 'pending')
                 ->where(fn ($q) =>
                     $q->whereNull('next_retry_at')
                       ->orWhere('next_retry_at', '<=', now())
                 );
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['pending', 'retrying']);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function pendingCount(): int
    {
        return static::active()->count();
    }

    /**
     * Calculate next retry delay using exponential backoff.
     * Attempt 0→5 min, 1→10, 2→20, 3→40, 4→60 (capped).
     */
    public function nextRetryDelay(): int
    {
        return (int) min(5 * pow(2, $this->attempts), 60);
    }

    public function markRetrying(): void
    {
        $this->update([
            'status'            => 'retrying',
            'last_attempted_at' => now(),
        ]);
    }

    public function markCompleted(int $billingSubscriptionId): void
    {
        $this->update([
            'status'                  => 'completed',
            'billing_subscription_id' => $billingSubscriptionId,
            'completed_at'            => now(),
            'last_error'              => null,
        ]);
    }

    public function markFailed(string $error): void
    {
        $newAttempts = $this->attempts + 1;
        $abandoned   = $newAttempts >= $this->max_attempts;

        $this->update([
            'status'         => $abandoned ? 'abandoned' : 'pending',
            'attempts'       => $newAttempts,
            'last_error'     => $error,
            'next_retry_at'  => $abandoned ? null : now()->addMinutes($this->nextRetryDelay()),
        ]);
    }

    /** Reset so it gets picked up on the next run. */
    public function retryNow(): void
    {
        $this->update([
            'status'        => 'pending',
            'next_retry_at' => now(),
            'last_error'    => null,
        ]);
    }
}
