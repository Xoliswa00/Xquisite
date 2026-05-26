<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\product_group;
use App\Models\product_category;

class ProductGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
      /*
        |--------------------------------------------------------------------------
        | GROUP: Operations & Digital Services
        |--------------------------------------------------------------------------
        */
        $operations = product_group::create([
            'name' => 'Operations & Digital Services',
            'slug' => 'operations-digital-services'
        ]);

        product_category::insert([
            [
                'name' => 'SLA',
                'slug' => 'sla',
                'product_group_id' => $operations->id
            ],
            [
                'name' => 'Analytics',
                'slug' => 'analytics',
                'product_group_id' => $operations->id
            ],
            [
                'name' => 'Finance Monitoring',
                'slug' => 'finance-monitoring',
                'product_group_id' => $operations->id
            ],
                [
                    'name' => 'System Health Checks',
                    'slug' => 'system-health-checks',
                    'product_group_id' => $operations->id
                ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | GROUP: Development & Systems
        |--------------------------------------------------------------------------
        */
        $development = product_group::create([
            'name' => 'Development & Systems',
            'slug' => 'development-systems'
        ]);

        product_category::insert([
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'product_group_id' => $development->id
            ],
            [
                'name' => 'System Modules',
                'slug' => 'system-modules',
                'product_group_id' => $development->id
            ],
            [
                'name' => 'API & Integrations',
                'slug' => 'api-integrations',
                'product_group_id' => $development->id
            ],
            [
                'name' => 'Custom Reports',
                'slug' => 'custom-reports',
                'product_group_id' => $development->id
            ],


        ]);

        /*
        |--------------------------------------------------------------------------
        | GROUP: Consulting & Finance
        |--------------------------------------------------------------------------
        */
        $consulting = product_group::create([
            'name' => 'Consulting & Finance',
            'slug' => 'consulting-finance'
        ]);

        product_category::insert([
            [
                'name' => 'Ad-Hoc Analysis',
                'slug' => 'adhoc-analysis',
                'product_group_id' => $consulting->id
            ],
            [
                'name' => 'Emergency Support',
                'slug' => 'emergency-support',
                'product_group_id' => $consulting->id
            ],
                [
                    'name' => 'Financial Consulting',
                    'slug' => 'financial-consulting',
                    'product_group_id' => $consulting->id
                ],
                [
                    'name' => 'Operational Consulting',
                    'slug' => 'operational-consulting',
                    'product_group_id' => $consulting->id
                ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | GROUP: Packages & Bundles
        |--------------------------------------------------------------------------
        */
        $packages = product_group::create([
            'name' => 'Packages & Bundles',
            'slug' => 'packages-bundles'
        ]);

        product_category::insert([
            [
                'name' => 'SLA Packages',
                'slug' => 'sla-packages',
                'product_group_id' => $packages->id
            ],
            [
                'name' => 'Development Packages',
                'slug' => 'development-packages',
                'product_group_id' => $packages->id
            ],
                [
                    'name' => 'Consulting Packages',
                    'slug' => 'consulting-packages',
                    'product_group_id' => $packages->id
                ],

        ]);
    }
    
}
