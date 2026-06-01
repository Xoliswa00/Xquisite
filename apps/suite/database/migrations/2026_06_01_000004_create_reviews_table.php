<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title', 120)->nullable();
            $table->text('body');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->string('business_type', 80)->nullable();
            $table->string('display_name', 80)->nullable();
            $table->unsignedSmallInteger('prompted_at_count')->nullable();
            $table->timestamps();
        });

        Schema::create('review_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('threshold');
            $table->timestamp('shown_at')->useCurrent();
            $table->timestamp('dismissed_at')->nullable();
            $table->foreignId('review_id')->nullable()->constrained()->nullOnDelete();
            $table->unique(['user_id', 'threshold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_prompts');
        Schema::dropIfExists('reviews');
    }
};
