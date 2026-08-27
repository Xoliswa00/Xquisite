<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->enum('deposit_status', ['held', 'refunded'])->default('held')->after('deposit_paid');
            $table->decimal('deposit_refund_amount', 10, 2)->nullable()->after('deposit_status');
            $table->date('deposit_refund_date')->nullable()->after('deposit_refund_amount');
            $table->enum('deposit_refund_method', ['eft', 'cash', 'card', 'debit_order', 'other'])->nullable()->after('deposit_refund_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['deposit_status', 'deposit_refund_amount', 'deposit_refund_date', 'deposit_refund_method']);
        });
    }
};
