<?php

namespace Database\Seeders;

use App\Models\PlatformService;
use Illuminate\Database\Seeder;

class PlatformServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // ── Onboarding ────────────────────────────────────────────────────
            [
                'key'            => 'onboarding_standard',
                'name'           => 'Platform Onboarding',
                'description'    => 'We set up your account for you — services, staff, products, and working hours configured. Includes a 1-hour video walkthrough so your team knows the system.',
                'category'       => 'onboarding',
                'billing_type'   => 'once_off',
                'price'          => 1500.00,
                'price_label'    => null,
                'icon'           => 'rocket',
                'is_requestable' => true,
                'sort_order'     => 1,
            ],
            [
                'key'            => 'data_import',
                'name'           => 'Data Import',
                'description'    => 'We import your existing customer list, product catalog, or booking history from spreadsheets or your old system.',
                'category'       => 'onboarding',
                'billing_type'   => 'once_off',
                'price'          => 750.00,
                'price_label'    => null,
                'icon'           => 'upload',
                'is_requestable' => true,
                'sort_order'     => 2,
            ],
            [
                'key'            => 'branded_setup',
                'name'           => 'Branded Setup',
                'description'    => 'We configure your public booking portal and storefront with your logo, brand colours, and business details.',
                'category'       => 'onboarding',
                'billing_type'   => 'once_off',
                'price'          => 500.00,
                'price_label'    => null,
                'icon'           => 'palette',
                'is_requestable' => true,
                'sort_order'     => 3,
            ],

            // ── Training ──────────────────────────────────────────────────────
            [
                'key'            => 'training_session',
                'name'           => 'Training Session',
                'description'    => 'A focused 1-hour video call for you and your team on any module — bookings, POS, property, or admin. Recorded and shared after.',
                'category'       => 'training',
                'billing_type'   => 'once_off',
                'price'          => 500.00,
                'price_label'    => null,
                'icon'           => 'academic',
                'is_requestable' => true,
                'sort_order'     => 4,
            ],

            // ── Support ───────────────────────────────────────────────────────
            [
                'key'            => 'priority_support',
                'name'           => 'Priority Support',
                'description'    => 'Skip the queue. Issues escalated to the top with a guaranteed 4-hour response time during business hours.',
                'category'       => 'support',
                'billing_type'   => 'recurring',
                'price'          => 299.00,
                'price_label'    => null,
                'icon'           => 'shield',
                'is_requestable' => true,
                'sort_order'     => 5,
            ],
            [
                'key'            => 'account_manager',
                'name'           => 'Dedicated Account Manager',
                'description'    => 'A named contact who knows your business. Monthly check-in call, proactive advice, and direct line for anything you need.',
                'category'       => 'support',
                'billing_type'   => 'recurring',
                'price'          => 799.00,
                'price_label'    => null,
                'icon'           => 'user_circle',
                'is_requestable' => true,
                'sort_order'     => 6,
            ],

            // ── Custom ────────────────────────────────────────────────────────
            [
                'key'            => 'custom_integration',
                'name'           => 'Custom Integration',
                'description'    => 'Need Xquisite to talk to your accounting software, booking platform, or any third-party system? We\'ll scope it and build it.',
                'category'       => 'custom',
                'billing_type'   => 'once_off',
                'price'          => null,
                'price_label'    => 'Custom quote',
                'icon'           => 'code',
                'is_requestable' => true,
                'sort_order'     => 7,
            ],
            [
                'key'            => 'custom_report',
                'name'           => 'Custom Report',
                'description'    => 'A bespoke report or dashboard built to your exact requirements — export-ready and scheduled to your inbox.',
                'category'       => 'custom',
                'billing_type'   => 'once_off',
                'price'          => null,
                'price_label'    => 'From R800',
                'icon'           => 'chart',
                'is_requestable' => true,
                'sort_order'     => 8,
            ],
        ];

        foreach ($services as $data) {
            PlatformService::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
