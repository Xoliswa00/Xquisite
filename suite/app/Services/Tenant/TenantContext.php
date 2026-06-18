<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\Auth;

class TenantContext
{
    protected static ?int $tenantId = null;

    public static function set(int $tenantId): void
    {
        self::$tenantId = $tenantId;
    }

    public static function get(): ?int
    {
        if (self::$tenantId !== null) {
            return self::$tenantId;
        }

        try {
            return Auth::user()?->tenant_id;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function clear(): void
    {
        self::$tenantId = null;
    }

    public static function hasTenant(): bool
    {
        return self::get() !== null;
    }
}
