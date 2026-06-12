<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('service_category_id')
                  ->nullable()
                  ->after('product_category_id')
                  ->constrained('service_categories')
                  ->nullOnDelete();

            $table->unsignedSmallInteger('duration_minutes')
                  ->nullable()
                  ->after('service_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_category_id');
            $table->dropColumn('duration_minutes');
        });
    }
};
