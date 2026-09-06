<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->text('booking_terms')->nullable()->after('shipping_cost');
            $table->boolean('require_booking_terms_acceptance')->default(false)->after('booking_terms');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['booking_terms', 'require_booking_terms_acceptance']);
        });
    }
};
