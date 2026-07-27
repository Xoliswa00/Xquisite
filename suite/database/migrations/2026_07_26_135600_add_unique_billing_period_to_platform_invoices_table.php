<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicates = DB::table('platform_invoices')
            ->select('tenant_id', 'billing_period_start')
            ->groupBy('tenant_id', 'billing_period_start')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $list = $duplicates->map(fn ($d) => "tenant {$d->tenant_id} / {$d->billing_period_start}")->implode(', ');
            throw new \RuntimeException(
                "Cannot add unique constraint: duplicate platform_invoices already exist for: {$list}. "
                . 'Resolve (merge/void) the duplicates before running this migration.'
            );
        }

        Schema::table('platform_invoices', function (Blueprint $table) {
            $table->unique(['tenant_id', 'billing_period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_invoices', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'billing_period_start']);
        });
    }
};
