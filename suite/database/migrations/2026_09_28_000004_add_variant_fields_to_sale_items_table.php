<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Mirrors order_items — see that migration for the rationale.
            // item_type/item_id stay 'product'/product_id unchanged for a
            // variant line, so every existing product-sales report keeps
            // working; these two columns are purely additive.
            $table->unsignedBigInteger('product_variant_id')->nullable()->after('item_id');
            $table->json('variant_attributes')->nullable()->after('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['product_variant_id', 'variant_attributes']);
        });
    }
};
