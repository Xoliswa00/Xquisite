<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * audit_logs(action) is a left prefix of audit_logs(action, created_at), so the
 * wider index answers everything the narrow one did, and every audit write was
 * maintaining both. Found by its columns, since index names differ between
 * databases that were not built from the same migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('audit_logs'));

        $wide   = $indexes->first(fn ($index) => $index['columns'] === ['action', 'created_at']);
        $narrow = $indexes->first(fn ($index) => ! $index['unique'] && $index['columns'] === ['action']);

        // Only ever drop the narrow one when the wide one is there to take over.
        if ($wide && $narrow) {
            Schema::table('audit_logs', fn (Blueprint $table) => $table->dropIndex($narrow['name']));
        }
    }

    public function down(): void
    {
        $exists = collect(Schema::getIndexes('audit_logs'))->contains(fn ($index) => $index['columns'] === ['action']);

        if (! $exists) {
            Schema::table('audit_logs', fn (Blueprint $table) => $table->index('action', 'audit_logs_action_index'));
        }
    }
};
