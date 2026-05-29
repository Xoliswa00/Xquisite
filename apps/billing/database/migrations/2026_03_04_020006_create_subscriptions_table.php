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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active'); // active|paused|cancelled
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('frequency')->default('monthly'); // monthly|quarterly|yearly
            $table->date('next_invoice_date');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('next_invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
