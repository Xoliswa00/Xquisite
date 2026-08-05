<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class SiteVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'path',
        'referrer',
        'visitor_hash',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeBetween(Builder $query, \DateTimeInterface $from, \DateTimeInterface $to): Builder
    {
        return $query->whereBetween('visited_at', [$from, $to]);
    }

    /**
     * Records a page view for a tenant's public site. Never lets a logging
     * failure break the page render, matching this app's existing
     * best-effort logging pattern (see ResolveTenant::logTenantMismatch).
     */
    public static function record(Tenant $tenant, string $path): void
    {
        try {
            static::create([
                'tenant_id'    => $tenant->id,
                'path'         => substr($path, 0, 255),
                'referrer'     => substr((string) Request::header('referer', ''), 0, 255) ?: null,
                'visitor_hash' => hash('sha256', Request::ip() . '|' . Request::userAgent()),
                'visited_at'   => now(),
            ]);
        } catch (\Throwable) {
            // Never let visit logging break a public page load.
        }
    }
}
