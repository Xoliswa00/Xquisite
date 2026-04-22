<?php

namespace App\Services\Tenant;

class TenantContext
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
       protected static ?int $tenantId = null;

    public static function set(int $tenantId): void
    {
        self::$tenantId = $tenantId;
    }

    public static function get(): ?int
    {
        return self::$tenantId ?? Auth::user()?->tenant_id;
    }

    public static function hasTenant(): bool
    {
        return self::get() !== null;
    }
}
