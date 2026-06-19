<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Single source of truth for the application's roles and permissions.
     *
     * Web-guard roles only — Customer and Renter authenticate against their own
     * dedicated guards (customer/renter) and separate models, so their access is
     * defined by the guard they log into, not by a spatie web role.
     */
    public function run(): void
    {
        $permissions = [
            // Platform operator
            'manage-tenants',
            'manage-plans',
            'manage-platform-modules',
            'manage-platform-billing',
            'view-system-logs',
            'review-module-requests',
            'approve-module-requests',
            // Business (tenant-scoped)
            'manage-staff',
            'manage-products',
            'manage-orders',
            'manage-appointments',
            'manage-customers',
            'view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $business = [
            'manage-staff', 'manage-products', 'manage-orders',
            'manage-appointments', 'manage-customers', 'view-reports',
        ];

        $roles = [
            // Platform operator — holds every permission. manage-tenants lives
            // here ONLY, so ordinary tenant owners can no longer reach the
            // platform-wide tenant admin (the previous escalation bug).
            'super-admin'  => $permissions,

            // Tenant business owner — full control of their own business, no
            // platform permissions.
            'tenant-owner' => $business,

            // Manager — same business scope as the owner (was the old "admin").
            'manager'      => $business,

            // Employee — day-to-day operations, cannot manage staff or products.
            'employee'     => ['manage-orders', 'manage-appointments', 'manage-customers', 'view-reports'],

            // Client portal user — no admin abilities.
            'client'       => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
