<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Nullable: absent for a plain (non-variant) product adjustment,
            // exactly as before. When present, product_id is still also set
            // (to the variant's parent) so every existing product-level
            // stock-history view keeps showing variant movements too.
            $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn('product_variant_id');
        });
    }
};
