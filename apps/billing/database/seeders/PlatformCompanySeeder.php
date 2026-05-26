<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class PlatformCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Company::updateOrCreate(
            ['name' => 'Xquisite Creations'],
            [
                'email' => 'admin@xquisitecreations.co.za',
                'phone' => '+27XXXXXXXXX',
                'platform_owner_id' => 1, // assumes seeded owner user
                'is_platform_company' => true,
                'slug' => 'xquisite-creations',
            ]
        );
    }
}
