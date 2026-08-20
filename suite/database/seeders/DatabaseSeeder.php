<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionRoleSeeder::class,
            PlatformServiceSeeder::class,
            PlanSeeder::class,
            DemoSeeder::class,
            PlatformModuleSeeder::class,
            ReviewSeeder::class,
        ]);

        // Example accounts — local and demo environments only.
        // Never run in production: change these credentials immediately after first deploy.
        if (app()->isLocal() || app()->environment('demo', 'staging')) {
            $systemOwner = User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'System Administrator',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'require_password_change' => true,
                ]
            );
            $systemOwner->syncRoles(['super-admin']);

            $testClient = User::updateOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            $testClient->syncRoles(['client']);
        }
    }
}
