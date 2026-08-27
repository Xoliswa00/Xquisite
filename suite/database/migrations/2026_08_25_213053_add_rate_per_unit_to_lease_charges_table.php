<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lease_charges', function (Blueprint $table) {
            $table->decimal('rate_per_unit', 10, 4)->nullable()->after('meter_reading_end');
            $table->foreignId('rent_payment_id')->nullable()->after('lease_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lease_charges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rent_payment_id');
            $table->dropColumn('rate_per_unit');
        });
    }
};
