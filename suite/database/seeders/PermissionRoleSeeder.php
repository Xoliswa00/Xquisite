<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Seed the application's permission and role data.
     */
    public function run(): void
    {
        $permissions = [
            'manage-tenants',
            'manage-staff',
            'view-reports',
            'manage-products',
            'manage-appointments',
            'manage-customers',
            'review-module-requests',
            'approve-module-requests',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'owner' => $permissions,
            'admin' => [
                'manage-staff',
                'view-reports',
                'manage-tenants',
                'manage-products',
                'manage-appointments',
                'manage-customers',
                'review-module-requests',
                'approve-module-requests',
            ],
            'staff' => [
                'view-reports',
                'manage-appointments',
                'manage-customers',
            ],
            'client' => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
