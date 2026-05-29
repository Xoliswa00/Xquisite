<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['create_subscription', 'cancel_subscription']);

            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('module_key');

            // All data needed to replay the sync when billing comes back
            $table->json('payload');

            $table->enum('status', ['pending', 'retrying', 'completed', 'abandoned'])
                  ->default('pending')
                  ->index();

            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(5);

            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('last_error')->nullable();

            // Filled in once billing confirms the subscription
            $table->unsignedBigInteger('billing_subscription_id')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
    }
};
