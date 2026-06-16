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
        Schema::dropIfExists('service_combo_items');

        Schema::create('service_combo_items', function (Blueprint $table) {
            $table->foreignId('service_combo_id')->constrained('service_combos')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->primary(['service_combo_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_combo_items');

        Schema::create('service_combo_items', function (Blueprint $table) {
            $table->foreignId('service_combo_id')->constrained('service_combos')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['service_combo_id', 'product_id']);
        });
    }
};
