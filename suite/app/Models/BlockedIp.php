<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class BlockedIp extends Model
{
    protected $fillable = ['ip_address', 'reason', 'blocked_by', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'blocked_by');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public static function isBlocked(string $ip): bool
    {
        return Cache::remember("blocked_ip:{$ip}", 60, fn () =>
            static::active()->where('ip_address', $ip)->exists()
        );
    }

    public static function block(string $ip, string $reason, ?int $blockedBy = null, ?int $expiresInMinutes = null): self
    {
        Cache::forget("blocked_ip:{$ip}");

        return static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason'     => $reason,
                'blocked_by' => $blockedBy,
                'expires_at' => $expiresInMinutes ? now()->addMinutes($expiresInMinutes) : null,
            ]
        );
    }

    public function unblock(): void
    {
        Cache::forget("blocked_ip:{$this->ip_address}");
        $this->delete();
    }

    public function isPermanent(): bool
    {
        return is_null($this->expires_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
