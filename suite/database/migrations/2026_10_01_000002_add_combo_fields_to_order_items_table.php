<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Same convention as product_variant_id on this table: no FK
            // constraint (a deleted/edited combo must never break order
            // history), combo_name frozen at purchase time. A combo line
            // becomes one OrderItem per constituent product (not one row
            // for the whole bundle) so existing stock-decrement/order-
            // display code needs no special "this row is a bundle" branch
            // — combo_id/combo_name are purely for display/traceability
            // ("this Ceramic Mug was part of the Starter Bundle") and for
            // pro-rating the bundle discount across its line items, not a
            // structural change to how an order's items are read.
            $table->unsignedBigInteger('combo_id')->nullable()->after('product_variant_id');
            $table->string('combo_name')->nullable()->after('combo_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['combo_id', 'combo_name']);
        });
    }
};
