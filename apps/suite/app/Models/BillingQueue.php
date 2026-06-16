<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BillingQueue extends Model
{
    protected $table = 'billing_queue';

    protected $fillable = [
        'tenant_id',
        'operation',
        'payload',
        'status',
        'attempts',
        'max_attempts',
        'scheduled_for',
        'next_retry_at',
        'last_attempted_at',
        'completed_at',
        'last_error',
        'result_invoice_id',
    ];

    protected $casts = [
        'payload'           => 'array',
        'scheduled_for'     => 'datetime',
        'next_retry_at'     => 'datetime',
        'last_attempted_at' => 'datetime',
        'completed_at'      => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resultInvoice()
    {
        return $this->belongsTo(PlatformInvoice::class, 'result_invoice_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeDue(Builder $q): Builder
    {
        return $q->where('status', 'pending')
                 ->where(fn ($q) =>
                     $q->whereNull('next_retry_at')
                       ->orWhere('next_retry_at', '<=', now())
                 )
                 ->where(fn ($q) =>
                     $q->whereNull('scheduled_for')
                       ->orWhere('scheduled_for', '<=', now())
                 );
    }

    // ── Helpers ──────────────────────────────────────────────────────

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

    public function markCompleted(?int $invoiceId = null): void
    {
        $this->update([
            'status'            => 'completed',
            'result_invoice_id' => $invoiceId,
            'completed_at'      => now(),
            'last_error'        => null,
        ]);
    }

    public function markFailed(string $error): void
    {
        $newAttempts = $this->attempts + 1;
        $abandoned   = $newAttempts >= $this->max_attempts;

        $this->update([
            'status'        => $abandoned ? 'abandoned' : 'pending',
            'attempts'      => $newAttempts,
            'last_error'    => $error,
            'next_retry_at' => $abandoned ? null : now()->addMinutes($this->nextRetryDelay()),
        ]);
    }

    public function retryNow(): void
    {
        $this->update([
            'status'        => 'pending',
            'next_retry_at' => now(),
            'last_error'    => null,
        ]);
    }

    public static function pendingCount(): int
    {
        return static::whereIn('status', ['pending', 'retrying'])->count();
    }
}
