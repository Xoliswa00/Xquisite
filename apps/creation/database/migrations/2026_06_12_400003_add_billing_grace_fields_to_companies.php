<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('grace_period_ends_at')->nullable()->after('subscription_renewal_date');
            $table->timestamp('suspended_at')->nullable()->after('grace_period_ends_at');
            $table->timestamp('last_billing_date')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['grace_period_ends_at', 'suspended_at', 'last_billing_date']);
        });
    }
};
