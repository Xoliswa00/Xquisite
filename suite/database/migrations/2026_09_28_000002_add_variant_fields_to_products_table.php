<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Declares the option axes this product is sold by, e.g.
            // {"Size":["S","M","L","XL"],"Color":["Red","Blue","Black"]}.
            // Null/empty for a product with no variants (the overwhelming
            // majority — this column is what the admin edits to generate
            // the product_variants grid, not something end users see raw).
            $table->json('variant_options')->nullable()->after('image_url');

            // Denormalized flag so listings/queries can check "does this
            // product need variant selection?" with an indexed column
            // instead of an exists() query against product_variants on
            // every single row of a product grid.
            $table->boolean('has_variants')->default(false)->after('variant_options');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['variant_options', 'has_variants']);
        });
    }
};
