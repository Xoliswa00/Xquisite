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
        Schema::create('lease_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['water', 'electricity', 'sewerage', 'levy', 'late_fee', 'discount', 'deposit', 'other'])->index();
            $table->string('description');
            $table->string('period')->nullable()->index(); // e.g. '2024-06' (YYYY-MM); null for one-off charges
            $table->decimal('amount_excl', 10, 2);
            $table->decimal('vat_rate', 5, 2)->default(0); // percentage, e.g. 15.00
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('amount_incl', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending')->index();
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->enum('payment_method', ['eft', 'cash', 'card', 'debit_order', 'other'])->nullable();
            $table->string('reference')->nullable();
            $table->decimal('meter_reading_start', 10, 2)->nullable();
            $table->decimal('meter_reading_end', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['lease_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_charges');
    }
};
