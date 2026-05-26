<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('reorder_level')->default(0)->after('stock_quantity');    // alert when stock <= this
            $table->integer('reorder_quantity')->default(0)->after('reorder_level'); // suggested qty to order
            $table->string('supplier')->nullable()->after('reorder_quantity');        // supplier name/contact
            $table->string('supplier_sku')->nullable()->after('supplier');            // supplier's product code
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reorder_level', 'reorder_quantity', 'supplier', 'supplier_sku']);
        });
    }
};
