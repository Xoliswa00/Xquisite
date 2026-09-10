<?php

namespace App\Support;

/**
 * Human-readable labels for Spatie permission slugs, for screens shown to
 * non-technical tenant owners (the Team / staff-account screens). Falls back to
 * a title-cased version of the slug for anything not listed.
 */
class PermissionLabels
{
    private const LABELS = [
        'manage-staff'        => 'Manage staff accounts',
        'manage-products'     => 'Manage products, services and pricing',
        'manage-orders'       => 'Process sales and orders',
        'manage-appointments' => 'Manage bookings and the calendar',
        'manage-customers'    => 'Manage customers',
        'manage-properties'   => 'Manage properties, leases and rent',
        'view-reports'        => 'View reports and revenue',
    ];

    public static function for(string $slug): string
    {
        return self::LABELS[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
    }
}
