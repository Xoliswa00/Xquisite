<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-party, cookieless traffic stats. No IP address or user id is stored.
 * visitor_hash is a one-way hash that changes every day, so the same person
 * cannot be followed from one day to the next.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_hash', 16);
            $table->string('path', 255);
            $table->string('source', 60)->nullable();
            $table->string('campaign', 100)->nullable();
            $table->string('referrer_host', 120)->nullable();
            $table->string('device', 10);
            $table->boolean('is_authenticated')->default(false);
            $table->unsignedTinyInteger('max_scroll')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['path', 'created_at']);
        });

        Schema::create('click_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_view_id')->constrained('page_views')->cascadeOnDelete();
            $table->string('path', 255);
            $table->decimal('x_pct', 5, 2);
            $table->unsignedInteger('y_px');
            $table->string('viewport', 10);
            $table->string('kind', 10);
            $table->string('label', 80)->nullable();
            $table->string('href', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['path', 'viewport']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('click_events');
        Schema::dropIfExists('page_views');
    }
};
