<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Nullable, no FK constraint — same rationale as order_items/
            // sale_items: a deleted/edited variant must never break a PO's
            // own record of what was ordered and received.
            $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');
            $table->json('variant_attributes')->nullable()->after('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['product_variant_id', 'variant_attributes']);
        });
    }
};
