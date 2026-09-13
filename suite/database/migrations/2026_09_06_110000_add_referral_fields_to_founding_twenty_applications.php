<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->foreignId('referred_by_tenant_id')->nullable()->after('tenant_id')
                ->constrained('tenants')->nullOnDelete();
            $table->timestamp('referral_reward_processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_tenant_id');
            $table->dropColumn('referral_reward_processed_at');
        });
    }
};
