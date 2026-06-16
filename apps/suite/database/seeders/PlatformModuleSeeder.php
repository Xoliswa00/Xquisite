<?php

namespace Database\Seeders;

use App\Models\PlatformModule;
use Illuminate\Database\Seeder;

class PlatformModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // ── Live ─────────────────────────────────────────────────────────
            [
                'key'           => 'booking',
                'name'          => 'Booking & Appointments',
                'description'   => 'Online and in-person appointment scheduling, customer management, and staff calendars.',
                'icon'          => 'calendar',
                'price'         => 199.00,
                'status'        => 'active',
                'auto_activate' => true,
                'sort_order'    => 1,
            ],
            [
                'key'           => 'pos',
                'name'          => 'Point of Sale',
                'description'   => 'POS terminal, sales history, product catalog, and stock management.',
                'icon'          => 'pos',
                'price'         => 299.00,
                'status'        => 'active',
                'auto_activate' => true,
                'sort_order'    => 2,
            ],
            [
                'key'           => 'ecommerce',
                'name'          => 'Online Store',
                'description'   => 'Public storefront, cart, checkout, PayFast payments, and order management.',
                'icon'          => 'store',
                'price'         => 249.00,
                'status'        => 'active',
                'auto_activate' => true,
                'sort_order'    => 3,
            ],
            [
                'key'           => 'property_management',
                'name'          => 'Property Management',
                'description'   => 'Manage rental properties, units, leases, rent payments, and maintenance requests. Includes a renter self-service portal.',
                'icon'          => 'building',
                'price'         => 349.00,
                'status'        => 'active',
                'auto_activate' => false,
                'sort_order'    => 4,
            ],
            // ── In Testing ───────────────────────────────────────────────────
            [
                'key'           => 'analytics',
                'name'          => 'Analytics & Reporting',
                'description'   => 'Revenue reports, booking trends, product performance, and exportable data.',
                'icon'          => 'chart',
                'price'         => 149.00,
                'status'        => 'beta',
                'auto_activate' => true,
                'sort_order'    => 5,
            ],
            [
                'key'           => 'online_booking',
                'name'          => 'Public Booking Portal',
                'description'   => 'A branded public page where your clients self-book appointments — no login required for the business owner.',
                'icon'          => 'widget',
                'price'         => 99.00,
                'status'        => 'beta',
                'auto_activate' => true,
                'sort_order'    => 6,
            ],
            // ── Coming Soon ──────────────────────────────────────────────────
            [
                'key'           => 'custom_domain',
                'name'          => 'Custom Domain',
                'description'   => 'Point your own domain (e.g. shop.yourbrand.co.za) to your Xquisite storefront.',
                'icon'          => 'domain',
                'price'         => 200.00,
                'status'        => 'coming_soon',
                'auto_activate' => false,
                'sort_order'    => 7,
            ],
            [
                'key'           => 'loyalty',
                'name'          => 'Loyalty & Rewards',
                'description'   => 'Points-based loyalty programme, reward redemption, and customer retention campaigns.',
                'icon'          => 'star',
                'price'         => 149.00,
                'status'        => 'coming_soon',
                'auto_activate' => false,
                'sort_order'    => 8,
            ],
            [
                'key'           => 'payroll',
                'name'          => 'Payroll & HR',
                'description'   => 'Staff payslips, leave management, and basic HR records — integrated with your team data.',
                'icon'          => 'users',
                'price'         => 299.00,
                'status'        => 'coming_soon',
                'auto_activate' => false,
                'sort_order'    => 9,
            ],
            [
                'key'           => 'multi_location',
                'name'          => 'Multi-Location',
                'description'   => 'Manage multiple branches under one account — separate stock, staff, and reports per location.',
                'icon'          => 'map',
                'price'         => 199.00,
                'status'        => 'coming_soon',
                'auto_activate' => false,
                'sort_order'    => 10,
            ],
            [
                'key'           => 'client_messaging',
                'name'          => 'Client Messaging',
                'description'   => 'Direct messaging channel between you and your business clients — with read receipts and in-app notifications.',
                'icon'          => 'chat',
                'price'         => 99.00,
                'status'        => 'active',
                'auto_activate' => false,
                'sort_order'    => 11,
            ],
        ];

        foreach ($modules as $data) {
            PlatformModule::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
