<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supports the /ingest/logs push endpoint (LogIngestController):
 *  - monitored_instances.slug — stable, rename-safe identifier written into
 *    system_logs.source for every forwarded row, so the central log viewer
 *    (/admin/logs?source=...) can filter by originating app.
 *  - system_logs.dedup_key — sha1(instance_id | reporter fingerprint); the
 *    ingest controller skips keys already present for a source, so a reporter
 *    re-sending a batch after a failed run can't create duplicates. Plain
 *    index, not a unique constraint: system_logs is a large shared table and
 *    a legacy row must never fail this migration — dedup is enforced in-app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitored_instances', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        Schema::table('system_logs', function (Blueprint $table) {
            $table->string('dedup_key', 64)->nullable()->after('source');
            $table->index(['source', 'dedup_key']);
        });
    }

    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropIndex(['source', 'dedup_key']);
            $table->dropColumn('dedup_key');
        });

        Schema::table('monitored_instances', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
