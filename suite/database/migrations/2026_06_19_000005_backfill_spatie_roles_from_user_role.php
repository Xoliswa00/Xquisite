<?php

use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Backfill spatie roles from the legacy users.role column before it is
     * dropped. Runs the canonical role/permission setup first so the roles
     * exist in every environment (including the test database), then maps each
     * existing user to its spatie role.
     */
    public function up(): void
    {
        (new PermissionRoleSeeder())->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (! DB::getSchemaBuilder()->hasColumn('users', 'role')) {
            return; // nothing to backfill
        }

        $roleIds = Role::pluck('id', 'name');

        DB::table('users')->orderBy('id')->chunkById(200, function ($users) use ($roleIds) {
            foreach ($users as $user) {
                $target = match ($user->role) {
                    'owner'  => $user->tenant_id === null ? 'super-admin' : 'tenant-owner',
                    'admin'  => 'manager',
                    'staff'  => 'employee',
                    'client' => 'client',
                    default  => null,
                };

                if (! $target || ! isset($roleIds[$target])) {
                    continue;
                }

                DB::table('model_has_roles')->updateOrInsert([
                    'role_id'    => $roleIds[$target],
                    'model_type' => User::class,
                    'model_id'   => $user->id,
                ], []);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // One-way data backfill; nothing to reverse.
    }
};
