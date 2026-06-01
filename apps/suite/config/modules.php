<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Modules
    |--------------------------------------------------------------------------
    | Each module has a key, display name, description, and monthly price (ZAR).
    | The 'routes' key lists route-name prefixes that require this module.
    |
    */

    // ── Live ─────────────────────────────────────────────────────────────────

    'booking' => [
        'name'          => 'Booking & Appointments',
        'description'   => 'Online and in-person appointment scheduling, customer management, and staff calendars.',
        'icon'          => 'calendar',
        'price'         => 199.00,
        'status'        => 'active',
        'auto_activate' => true,
        'routes'        => ['appointments.*', 'customers.*', 'staff.*', 'services.*'],
    ],

    'pos' => [
        'name'          => 'Point of Sale',
        'description'   => 'POS terminal, sales history, product catalog, and stock management.',
        'icon'          => 'pos',
        'price'         => 299.00,
        'status'        => 'active',
        'auto_activate' => true,
        'routes'        => ['pos.*', 'products.*', 'stock.*', 'purchase-orders.*', 'suppliers.*'],
    ],

    'ecommerce' => [
        'name'          => 'Online Store',
        'description'   => 'Public storefront, cart, checkout, PayFast payments, and order management.',
        'icon'          => 'store',
        'price'         => 249.00,
        'status'        => 'active',
        'auto_activate' => true,
        'routes'        => ['orders.*', 'shop.*'],
    ],

    'property_management' => [
        'name'          => 'Property Management',
        'description'   => 'Manage rental properties, units, leases, rent payments, and maintenance requests. Includes a renter self-service portal.',
        'icon'          => 'building',
        'price'         => 349.00,
        'status'        => 'active',
        'auto_activate' => false,
        'routes'        => ['properties.*', 'units.*', 'renters.*', 'leases.*', 'rent.*', 'maintenance.*'],
    ],

    // ── In Testing ────────────────────────────────────────────────────────────

    'analytics' => [
        'name'          => 'Analytics & Reporting',
        'description'   => 'Revenue reports, booking trends, product performance, and exportable data.',
        'icon'          => 'chart',
        'price'         => 149.00,
        'status'        => 'beta',
        'auto_activate' => true,
        'routes'        => ['analytics.*'],
    ],

    'online_booking' => [
        'name'          => 'Public Booking Portal',
        'description'   => 'A branded public page where your clients self-book appointments — no login required for the business owner.',
        'icon'          => 'widget',
        'price'         => 99.00,
        'status'        => 'beta',
        'auto_activate' => true,
        'routes'        => ['book.*'],
    ],

    // ── Coming Soon ───────────────────────────────────────────────────────────

    'custom_domain' => [
        'name'          => 'Custom Domain',
        'description'   => 'Point your own domain (e.g. shop.yourbrand.co.za) to your Xquisite storefront.',
        'icon'          => 'domain',
        'price'         => 200.00,
        'status'        => 'coming_soon',
        'auto_activate' => false,
        'routes'        => [],
    ],

    'loyalty' => [
        'name'          => 'Loyalty & Rewards',
        'description'   => 'Points-based loyalty programme, reward redemption, and customer retention campaigns.',
        'icon'          => 'star',
        'price'         => 149.00,
        'status'        => 'coming_soon',
        'auto_activate' => false,
        'routes'        => [],
    ],

    'payroll' => [
        'name'          => 'Payroll & HR',
        'description'   => 'Staff payslips, leave management, and basic HR records — integrated with your team data.',
        'icon'          => 'users',
        'price'         => 299.00,
        'status'        => 'coming_soon',
        'auto_activate' => false,
        'routes'        => [],
    ],

    'multi_location' => [
        'name'          => 'Multi-Location',
        'description'   => 'Manage multiple branches under one account — separate stock, staff, and reports per location.',
        'icon'          => 'map',
        'price'         => 199.00,
        'status'        => 'coming_soon',
        'auto_activate' => false,
        'routes'        => [],
    ],

];
