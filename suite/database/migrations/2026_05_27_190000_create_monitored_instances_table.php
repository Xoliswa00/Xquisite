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
        Schema::create('monitored_instances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('api_token')->encrypted();
            $table->string('tenant_id')->nullable();
            $table->enum('status', ['up', 'down', 'unknown'])->default('unknown');
            $table->decimal('uptime_percentage', 5, 2)->default(100);
            $table->timestamp('last_check_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('status');
            $table->index('is_active');
        });

        Schema::create('health_check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_instance_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['up', 'down'])->default('up');
            $table->integer('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
            $table->index('monitored_instance_id');
            $table->index('checked_at');
        });

        Schema::create('instance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_instance_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['down', 'up', 'error'])->default('down');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index('monitored_instance_id');
            $table->index('type');
            $table->index('is_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instance_alerts');
        Schema::dropIfExists('health_check_logs');
        Schema::dropIfExists('monitored_instances');
    }
};
