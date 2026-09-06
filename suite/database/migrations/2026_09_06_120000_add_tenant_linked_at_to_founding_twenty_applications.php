<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // Anchor date for the 30/60/90-day check-in schedule — separate from
            // tenant_id itself so "days since onboarded" is always computable,
            // even though tenant_id already existing doesn't tell you when.
            $table->timestamp('tenant_linked_at')->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropColumn('tenant_linked_at');
        });
    }
};
