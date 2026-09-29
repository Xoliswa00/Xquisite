<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A second intake into the same Founding 20 programme, for businesses that don't
 * run on bookings/appointments (the whole scored questionnaire assumes that) but
 * still want a custom solution. Same 3-months-free offer, same review pipeline,
 * same deposit/onboarding — only the intake and the post-trial price differ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // 'booking' is the existing scored questionnaire; 'custom' skips it entirely.
            $table->string('track', 10)->default('booking')->after('business_type_other');
            $table->text('custom_solution_description')->nullable()->after('track');

            // Set once (if ever) the thing they needed becomes a real, reusable module —
            // otherwise the applicant is quoted individually outside this table for now.
            $table->decimal('custom_monthly_price', 8, 2)->nullable()->after('custom_solution_description');
            $table->text('custom_pricing_notes')->nullable()->after('custom_monthly_price');
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropColumn(['track', 'custom_solution_description', 'custom_monthly_price', 'custom_pricing_notes']);
        });
    }
};
