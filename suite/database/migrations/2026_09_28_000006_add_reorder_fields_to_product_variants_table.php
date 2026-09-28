<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-variant, not just per-product — demand genuinely differs by size/color
 * (a clothing store sells more Mediums than XS), so a single reorder_level
 * on the parent product can't represent "reorder Medium/Red at 5 but
 * Small/Blue at 2". Null on a variant falls back to the parent product's
 * own reorder_level/reorder_quantity, same inheritance pattern as
 * price_override.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('reorder_level')->nullable()->after('stock_quantity');
            $table->unsignedInteger('reorder_quantity')->nullable()->after('reorder_level');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['reorder_level', 'reorder_quantity']);
        });
    }
};
