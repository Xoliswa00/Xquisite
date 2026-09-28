<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A product can be sold as-is (stock/price live on `products`, unchanged —
 * every existing tenant keeps working exactly as before) or, once it has
 * `variant_options` set, as a set of concrete combinations tracked here —
 * e.g. a T-shirt with options {"Size":["S","M","L"],"Color":["Red","Blue"]}
 * gets one row per Size×Color combination actually stocked. Attributes are
 * JSON, not fixed columns, because the option set is per-product, not
 * platform-wide — a hardware store's variants look nothing like a clothing
 * store's, and this is a multi-tenant, multi-industry platform.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            // e.g. {"Size":"M","Color":"Red"} — keys are whatever option
            // names the parent product declared in products.variant_options.
            $table->json('attributes');
            // null = inherit the parent product's price/stock-tracking.
            $table->decimal('price_override', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
