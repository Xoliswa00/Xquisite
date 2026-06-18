<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_invoices', function (Blueprint $table) {
            $table->index(['status', 'paid_at']);
            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'due_date']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->index(['is_active', 'suspended_at']);
            $table->index('trial_ends_at');
            $table->index('grace_period_ends_at');
            $table->dateTime('last_grace_warning_sent_at')->nullable()->after('grace_period_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('last_grace_warning_sent_at');
            $table->dropIndex(['grace_period_ends_at']);
            $table->dropIndex(['trial_ends_at']);
            $table->dropIndex(['is_active', 'suspended_at']);
        });

        Schema::table('platform_invoices', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['status', 'paid_at']);
        });
    }
};
