<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors service_photos' final shape (built there across two migrations,
 * created fresh here in one since there's no legacy data to carry forward).
 * Deliberately a standalone table with a hard product_id FK, not a
 * polymorphic redesign of service_photos — that table has real production
 * data and an active moderation workflow with zero existing test coverage;
 * reshaping it now for a brand-new feature is a separate, higher-risk piece
 * of work with no upside here. See ProductPhoto's docblock for the same
 * reasoning on the model side.
 *
 * No product_photo_reports table (yet) — the public "report this photo"
 * entry point from the booking portal isn't being built for the shop in
 * this pass; hidden_at/hidden_reason still support admin-side hide/unhide
 * on their own, just without a public flagging flow driving it.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('disk', 20)->default('public');
            $table->string('path_web')->nullable();
            $table->string('path_thumb')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('alt_text', 160)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('hidden_at')->nullable();
            $table->string('hidden_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
            // Storefront reads "visible photos for this product" on every page hit.
            $table->index(['product_id', 'hidden_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_photos');
    }
};
