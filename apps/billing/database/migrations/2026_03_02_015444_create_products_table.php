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
        Schema::create('products', function (Blueprint $table) {
                $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_group_id')->nullable()->constrained()->nullOnDelete(); 
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();  
                            
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['service', 'product'])->default('service');
            $table->enum('billing_type', ['once_off', 'recurring'])->default('once_off');
            $table->enum('billing_cycle', ['monthly','yearly'])->nullable();




        $table->boolean('is_active')->default(true);

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
