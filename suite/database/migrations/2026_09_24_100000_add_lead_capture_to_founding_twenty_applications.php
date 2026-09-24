<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // The person, not just the business. Captured in the short first step so
            // we know who they are even if they never finish the questionnaire.
            $table->string('applicant_role')->nullable()->after('owner_name');
            $table->text('why_founding_20')->nullable()->after('applicant_role');
            $table->string('heard_about_via')->nullable()->after('why_founding_20');

            // Null until the questionnaire is actually submitted. A row with no
            // submitted_at is a lead: someone who gave their details but hasn't finished.
            $table->timestamp('submitted_at')->nullable()->after('privacy_consented_at');

            // A lead has no business type yet.
            $table->string('business_type')->nullable()->change();
        });

        // Everything that exists today came through the old single-step form, so it was
        // submitted at the moment it was created.
        DB::table('founding_twenty_applications')->whereNull('submitted_at')->update(['submitted_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropColumn(['applicant_role', 'why_founding_20', 'heard_about_via', 'submitted_at']);
        });
    }
};
