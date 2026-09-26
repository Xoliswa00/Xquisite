<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // What the business chose for its deposit: 'refund' or 'credit'.
            $table->string('deposit_outcome', 10)->nullable();
            $table->timestamp('deposit_outcome_at')->nullable();
            $table->string('deposit_refund_reference', 100)->nullable();
            $table->timestamp('deposit_credited_at')->nullable();
            $table->foreignId('deposit_credit_invoice_id')->nullable()->constrained('platform_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deposit_credit_invoice_id');
            $table->dropColumn(['deposit_outcome', 'deposit_outcome_at', 'deposit_refund_reference', 'deposit_credited_at']);
        });
    }
};
