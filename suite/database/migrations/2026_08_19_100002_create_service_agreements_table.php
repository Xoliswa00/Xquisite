<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sla_plan_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('service_type', [
                'website_hosting', 'pos_erp_support', 'automation_support',
                'reporting_support', 'general_support', 'other',
            ])->default('website_hosting');

            $table->string('plan_name');
            $table->decimal('monthly_fee', 10, 2);
            $table->unsignedInteger('minutes_allowance')->default(30);
            $table->date('start_date');
            $table->unsignedInteger('commitment_months')->default(12);
            $table->unsignedTinyInteger('billing_day')->default(1);

            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending')->index();

            $table->enum('late_stage', ['current', 'reminder_1', 'reminder_2', 'suspended'])->default('current');
            $table->string('last_reminder_stage_sent')->nullable();

            $table->timestamp('suspended_at')->nullable();
            $table->date('terminated_at')->nullable();
            $table->string('termination_reason')->nullable();
            $table->decimal('reactivation_fee', 10, 2)->default(350.00);

            $table->date('accepted_at')->nullable();
            $table->string('accepted_name')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('billing_subscription_id')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_agreements');
    }
};
