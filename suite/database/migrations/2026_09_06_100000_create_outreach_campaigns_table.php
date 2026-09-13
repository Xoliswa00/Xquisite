<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel')->nullable(); // tiktok, whatsapp, poster, referral, etc.
            $table->string('target_business_type')->nullable(); // null = any industry
            $table->unsignedInteger('planned_count')->nullable();
            $table->enum('status', ['planned', 'active', 'completed'])->default('planned');
            $table->date('started_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->foreignId('outreach_campaign_id')->nullable()->after('id')
                ->constrained('outreach_campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outreach_campaign_id');
        });
        Schema::dropIfExists('outreach_campaigns');
    }
};
