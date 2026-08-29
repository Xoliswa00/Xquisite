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
        Schema::create('unit_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_request_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('category', ['maintenance', 'rates', 'insurance', 'levy', 'other'])->index();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'unit_id']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_expenses');
    }
};
