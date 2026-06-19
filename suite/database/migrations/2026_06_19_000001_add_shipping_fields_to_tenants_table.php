<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('shipping_enabled')->default(false)->after('logo_url');
            $table->string('shipping_type')->default('flat')->after('shipping_enabled'); // flat | free
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('shipping_type');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['shipping_enabled', 'shipping_type', 'shipping_cost']);
        });
    }
};
