<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Type of movement
            $table->string('type');
            // sale        = stock out from a POS sale
            // stocktake   = physical count correction
            // receive     = stock received from purchase order
            // adjustment_in  = manual increase
            // adjustment_out = manual decrease

            $table->integer('quantity_before');
            $table->integer('quantity_change'); // positive = in, negative = out
            $table->integer('quantity_after');

            // Optional links
            $table->unsignedBigInteger('sale_id')->nullable()->index();
            $table->unsignedBigInteger('purchase_order_id')->nullable()->index();

            $table->string('reference')->nullable(); // PO ref, sale ref, etc.
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // user_id
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
