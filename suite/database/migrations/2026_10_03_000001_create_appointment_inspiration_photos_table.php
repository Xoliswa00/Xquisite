<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer-uploaded "this is the look I want" photos attached to a booking.
 *
 * Unlike service_photos/product_photos these are personal uploads (often a
 * photo of the customer themselves), so they live on the PRIVATE disk and are
 * only ever streamed through an authorised route — never a public URL. No
 * original is kept: the upload is re-encoded on arrival (strips EXIF/GPS),
 * so `path` is already the cleaned display copy.
 *
 * services.accepts_inspiration_photos lets an owner switch the upload off for
 * services where it makes no sense (a consultation, a deposit-only slot).
 * Defaults on, since the section is optional for the customer anyway.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_inspiration_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('path_thumb')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['appointment_id', 'sort_order'], 'appt_inspo_photos_order_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->text('inspiration_notes')->nullable()->after('notes');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->boolean('accepts_inspiration_photos')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('accepts_inspiration_photos');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('inspiration_notes');
        });

        Schema::dropIfExists('appointment_inspiration_photos');
    }
};
