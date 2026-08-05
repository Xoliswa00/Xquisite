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
        Schema::table('tenants', function (Blueprint $table) {
            // The industry template a trial tenant picked but can't activate yet —
            // remembered so the marketplace can highlight it and offer a one-click
            // activate once they're off trial. No FK: mirrors the tenant_templates
            // convention of joining Template by its string key.
            $table->string('preferred_template_key')->nullable()->after('industry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('preferred_template_key');
        });
    }
};
