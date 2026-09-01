<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * /api/health-report looks up MonitoredInstance by api_token on every
     * single request from an external instance (see HealthReportController)
     * — with no index this was a full table scan every time, caught as a
     * 2.8s [SlowQuery] in production on 2026-08-30.
     */
    public function up(): void
    {
        Schema::table('monitored_instances', function (Blueprint $table) {
            $table->index('api_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitored_instances', function (Blueprint $table) {
            $table->dropIndex(['api_token']);
        });
    }
};
