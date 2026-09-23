<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_checkins', function (Blueprint $table) {
            // Asking "what would make you continue/cancel at R200/month" only makes sense
            // once someone has actually used the platform — moved here from the intake
            // questionnaire, where it was pure speculation before day one. Shown on the
            // 90-day check-in specifically, since that's the point the free period ends.
            $table->text('continuation_driver')->nullable()->after('continuation_likelihood');
            $table->text('churn_driver')->nullable()->after('continuation_driver');
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_checkins', function (Blueprint $table) {
            $table->dropColumn(['continuation_driver', 'churn_driver']);
        });
    }
};
