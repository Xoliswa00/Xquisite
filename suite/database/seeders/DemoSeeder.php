<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo tenant ───────────────────────────────────────────────────────
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'demo'],
            [
                'name'        => 'Xquisite Demo Business',
                'subdomain'   => 'demo',
                'email'       => 'demo@xquisite.co.za',
                'phone'       => '+27 00 000 0000',
                'plan'        => 'demo',
                'industry'    => 'Retail & Services',
                'is_active'   => true,
                'is_demo'     => true,
                'trial_ends_at' => now()->addYears(10),
            ]
        );

        // ── Demo owner user ───────────────────────────────────────────────────
        $owner = User::updateOrCreate(
            ['email' => 'demo@xquisite.co.za'],
            [
                'name'                   => 'Demo Owner',
                'password'               => Hash::make('demo1234'),
                'tenant_id'              => $tenant->id,
                'is_active'              => true,
                'require_password_change' => false,
            ]
        );
        $owner->syncRoles(['tenant-owner']);

        // ── Activate all live modules for demo ────────────────────────────────
        $modules = ['booking', 'pos', 'ecommerce', 'analytics', 'property_management'];

        foreach ($modules as $module) {
            TenantModule::updateOrCreate(
                ['tenant_id' => $tenant->id, 'module' => $module],
                [
                    'is_active'    => true,
                    'activated_at' => now(),
                    'activated_by' => $owner->id,
                ]
            );
        }
    }
}
