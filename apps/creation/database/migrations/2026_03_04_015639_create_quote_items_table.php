<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
    $table->unsignedBigInteger('quote_id');
    $table->foreign('quote_id')->references('id')->on('quotes')->cascadeOnDelete();
    $table->unsignedBigInteger('product_id');
    $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
    $table->unsignedBigInteger('product_item')->nullable();
    $table->foreign('product_item')->references('id')->on('product_items')->cascadeOnDelete();
    $table->string('description')->nullable();
    

    
    $table->integer('quantity')->default(1);
    $table->decimal('unit_price', 15, 2);
    $table->decimal('vat_amount', 15, 2)->default(0);
    $table->decimal('total', 15, 2)->default(0);
    $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
