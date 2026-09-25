<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // Which messages the applicant has actually been sent, so nobody is left in silence.
            $table->timestamp('received_notified_at')->nullable();
            $table->timestamp('decision_notified_at')->nullable();
            $table->timestamp('activation_nudge_sent_at')->nullable();
            $table->timestamp('conversion_offer_sent_at')->nullable();

            // Furthest questionnaire section reached (1-8), for drop-off analysis.
            $table->unsignedTinyInteger('last_section_reached')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropColumn(['received_notified_at', 'decision_notified_at', 'activation_nudge_sent_at', 'conversion_offer_sent_at', 'last_section_reached']);
        });
    }
};
