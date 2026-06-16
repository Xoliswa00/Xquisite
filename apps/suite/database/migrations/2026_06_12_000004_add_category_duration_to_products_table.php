<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('service_category_id')->nullable()->after('category')->constrained('service_categories')->nullOnDelete();
            $table->unsignedInteger('duration_minutes')->nullable()->after('service_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ServiceCategory::class);
            $table->dropColumn(['service_category_id', 'duration_minutes']);
        });
    }
};
