<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->enum('pricing_type', ['flat', 'per_head', 'per_unit'])->default('flat')->after('price');
            $table->decimal('price_per_unit', 10, 2)->nullable()->after('pricing_type');
            $table->string('unit_label', 30)->nullable()->after('price_per_unit');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'price_per_unit', 'unit_label']);
        });
    }
};
