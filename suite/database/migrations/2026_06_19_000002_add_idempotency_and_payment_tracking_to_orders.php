<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Guards against duplicate orders from double-submits / retried requests.
            $table->string('idempotency_key')->nullable()->after('reference');
            $table->unique(['tenant_id', 'idempotency_key']);

            // When a gateway payment was initiated — used to expire stale pending orders.
            $table->timestamp('payment_initiated_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'idempotency_key']);
            $table->dropColumn(['idempotency_key', 'payment_initiated_at']);
        });
    }
};
