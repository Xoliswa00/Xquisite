<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PlatformOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::updateOrCreate(
            ['email' => 'owner@platform.com'],
            [
                'name' => 'Platform Owner',
                'password' => Hash::make('ChangeThisPassword'),
                'role' => 'platform_owner',
                'is_platform_owner' => true,
            ]
        );
    }
}
