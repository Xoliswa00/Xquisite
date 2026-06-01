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
            PlatformModuleSeeder::class,
        ]);

        $systemOwner = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'is_active' => true,
                'require_password_change' => true,
            ]
        );
        $systemOwner->assignRole('owner');

        $testClient = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'client',
                'is_active' => true,
            ]
        );
        $testClient->syncRoles(['client']);
    }
}
