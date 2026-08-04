<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Second batch of tenant_id -> tenants.id FKs (see
     * 2026_06_19_000003_add_tenant_foreign_keys.php for the first batch and
     * the cascade rationale: tenants soft-delete, so cascade only fires on
     * an explicit forceDelete — normal deletion keeps all data).
     */
    private array $tables = [
        'services', 'staff', 'staff_blocks', 'staff_schedules',
        'sales', 'stock_adjustments', 'purchase_orders', 'suppliers',
        'properties', 'renters', 'rent_payments', 'maintenance_requests',
        'appointment_reminders', 'service_products',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'tenant_id')) {
                DB::table($table)
                    ->whereNotNull('tenant_id')
                    ->whereNotIn('tenant_id', DB::table('tenants')->select('id'))
                    ->update(['tenant_id' => null]);
            }
        }

        // SQLite cannot ALTER TABLE ADD CONSTRAINT — skip there (prod is MySQL/MariaDB).
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
