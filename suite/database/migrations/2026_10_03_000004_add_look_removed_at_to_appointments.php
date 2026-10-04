<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records that the client removed a saved look, so staff can't quietly save
 * it again (and restart the 2-year retention) against the client's choice.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('look_removed_at')->nullable()->after('look_saved_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('look_removed_at');
        });
    }
};
