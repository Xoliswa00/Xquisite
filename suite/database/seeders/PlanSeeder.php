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
                'price_monthly' => 449.00,
                'price_annual'  => 379.00,
                'is_active'     => false,
                'is_featured'   => false,
                'sort_order'    => 1,
                'modules'       => ['booking', 'pos'],
            ],
            [
                'key'           => 'growth',
                'name'          => 'Growth',
                'tagline'       => 'For businesses ready to sell online and track performance',
                'description'   => 'Everything in Starter plus an online store and analytics so you can grow beyond walk-ins.',
                'price_monthly' => 749.00,
                'price_annual'  => 629.00,
                'is_active'     => false,
                'is_featured'   => true,
                'sort_order'    => 2,
                'modules'       => ['booking', 'pos', 'ecommerce', 'analytics'],
            ],
            [
                'key'           => 'scale',
                'name'          => 'Scale',
                'tagline'       => 'Full platform access — all current and upcoming live modules',
                'description'   => 'Every live module included. New modules added automatically as they launch.',
                'price_monthly' => 999.00,
                'price_annual'  => 849.00,
                'is_active'     => false,
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
