<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // Links the application to the real tenant once one is created —
            // closes the gap between "selected" and the rest of onboarding,
            // which otherwise had no recorded connection to each other.
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // The single highest-leverage onboarding step identified by the
            // go-to-market review: SMB churn concentrates in the first 90 days,
            // and hitting a real win in week one roughly halves it. Tracked
            // explicitly so it isn't quietly skipped in favour of the 30/60/90
            // check-in cadence alone.
            $table->timestamp('first_value_milestone_at')->nullable();
            $table->string('first_value_milestone_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['first_value_milestone_at', 'first_value_milestone_note']);
        });
    }
};
