<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Nullable, no FK constraint — same rationale as product_id on
            // this table: a deleted/edited variant must never break order
            // history. variant_attributes is a frozen snapshot taken at
            // purchase time (e.g. {"Size":"M","Color":"Red"}), same pattern
            // already used for the invoice line-item snapshot elsewhere in
            // this codebase — what the customer actually bought never
            // changes even if the admin later renames or deletes the variant.
            $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');
            $table->json('variant_attributes')->nullable()->after('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_variant_id', 'variant_attributes']);
        });
    }
};
