<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guards POS checkout against duplicate submissions (double-click,
     * refresh-and-resubmit, replayed offline queue entries) the same way
     * orders.idempotency_key guards the online storefront checkout.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->after('reference');
            $table->unique(['tenant_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
