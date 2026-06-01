<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanModule;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key'           => 'starter',
                'name'          => 'Starter',
                'tagline'       => 'Perfect for service businesses just getting started',
                'description'   => 'Appointments, staff management, and a POS terminal — everything you need to run day-to-day operations.',
                'price_monthly' => 349.00,
                'price_annual'  => 299.00,
                'is_active'     => true,
                'is_featured'   => false,
                'sort_order'    => 1,
                'modules'       => ['booking', 'pos'],
            ],
            [
                'key'           => 'growth',
                'name'          => 'Growth',
                'tagline'       => 'For businesses ready to sell online and track performance',
                'description'   => 'Everything in Starter plus an online store and analytics so you can grow beyond walk-ins.',
                'price_monthly' => 599.00,
                'price_annual'  => 499.00,
                'is_active'     => true,
                'is_featured'   => true,
                'sort_order'    => 2,
                'modules'       => ['booking', 'pos', 'ecommerce', 'analytics'],
            ],
            [
                'key'           => 'scale',
                'name'          => 'Scale',
                'tagline'       => 'Full platform access — all current and upcoming live modules',
                'description'   => 'Every live module included. New modules added automatically as they launch.',
                'price_monthly' => 899.00,
                'price_annual'  => 749.00,
                'is_active'     => true,
                'is_featured'   => false,
                'sort_order'    => 3,
                'modules'       => ['booking', 'pos', 'ecommerce', 'analytics', 'property_management', 'online_booking'],
            ],
        ];

        foreach ($plans as $data) {
            $modules = $data['modules'];
            unset($data['modules']);

            $plan = Plan::updateOrCreate(['key' => $data['key']], $data);

            // Sync modules — delete old, insert fresh
            PlanModule::where('plan_id', $plan->id)->delete();

            foreach ($modules as $moduleKey) {
                PlanModule::create([
                    'plan_id'    => $plan->id,
                    'module_key' => $moduleKey,
                ]);
            }
        }
    }
}
