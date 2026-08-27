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
        Schema::table('leases', function (Blueprint $table) {
            // Which expiry-warning thresholds (30/14/7/1) have already been sent,
            // so a scheduler gap doesn't cause a lease to miss a milestone forever —
            // the next run just catches up instead of re-matching an exact date.
            $table->json('notified_thresholds')->nullable()->after('end_date');
            $table->index(['status', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndex(['status', 'end_date']);
            $table->dropColumn('notified_thresholds');
        });
    }
};
