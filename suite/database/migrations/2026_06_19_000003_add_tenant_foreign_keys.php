<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables whose tenant_id should reference tenants.id.
     *
     * cascadeOnDelete: tenant-owned operational data is purged with the tenant.
     * Tenants soft-delete in this app, so the cascade only fires on an explicit
     * hard purge (forceDelete) — normal deletion keeps all data.
     */
    private array $tables = ['users', 'products', 'orders', 'appointments', 'customers', 'leases', 'units'];

    public function up(): void
    {
        // 1. Null out any orphaned tenant_id (pointing at a missing tenant) so
        //    the constraint can be added and "no orphaned records" holds.
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'tenant_id')) {
                DB::table($table)
                    ->whereNotNull('tenant_id')
                    ->whereNotIn('tenant_id', DB::table('tenants')->select('id'))
                    ->update(['tenant_id' => null]);
            }
        }

        // 2. SQLite cannot ALTER TABLE ADD CONSTRAINT — skip there (prod is MySQL/MariaDB).
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('tenant_id')
                    ->references('id')->on('tenants')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['tenant_id']);
            });
        }
    }
};
