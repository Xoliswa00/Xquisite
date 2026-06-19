<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clamp any pre-existing negative stock before tightening the column,
        // otherwise the unsigned constraint would reject the change.
        DB::table('products')->where('stock_quantity', '<', 0)->update(['stock_quantity' => 0]);

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock_quantity')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
        });
    }
};
