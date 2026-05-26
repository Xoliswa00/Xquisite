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

    'booking' => [
        'name'        => 'Booking & Appointments',
        'description' => 'Online and in-person appointment scheduling, customer management, staff calendars.',
        'icon'        => 'calendar',
        'price'       => 199.00,
        'routes'      => ['appointments.*', 'customers.*', 'staff.*', 'services.*'],
    ],

    'pos' => [
        'name'        => 'Point of Sale',
        'description' => 'POS terminal, sales history, product catalog, and stock management.',
        'icon'        => 'pos',
        'price'       => 299.00,
        'routes'      => ['pos.*', 'products.*', 'stock.*', 'purchase-orders.*', 'suppliers.*'],
    ],

    'ecommerce' => [
        'name'        => 'Online Store',
        'description' => 'Public storefront, cart, checkout, PayFast payments, and order management.',
        'icon'        => 'store',
        'price'       => 249.00,
        'routes'      => ['orders.*', 'shop.*'],
    ],

    'online_booking' => [
        'name'        => 'Public Booking Widget',
        'description' => 'Embeddable booking widget and public-facing booking page for clients to self-book.',
        'icon'        => 'widget',
        'price'       => 99.00,
        'routes'      => ['booking.widget.*'],
    ],

    'analytics' => [
        'name'        => 'Analytics & Reporting',
        'description' => 'Revenue reports, booking trends, product performance, and exportable data.',
        'icon'        => 'chart',
        'price'       => 149.00,
        'routes'      => ['analytics.*'],
    ],

    'custom_domain' => [
        'name'        => 'Custom Domain',
        'description' => 'Point your own domain (e.g. shop.yourbrand.co.za) to your Xquisite storefront.',
        'icon'        => 'domain',
        'price'       => 200.00,
        'routes'      => [],
    ],

];
