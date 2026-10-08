<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Recent rows for one action" (the sign-in pause list on the Blocked IPs
 * screen) could only use the single-column action index, which walks every row
 * for that action ever written.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at'], 'audit_logs_action_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_action_created_idx');
        });
    }
};
