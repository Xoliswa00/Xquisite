<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saved looks: staff can add "after" photos to a booking (kind = result) and
 * save the booking as the client's look. A saved look is kept for 2 years
 * instead of the usual 90 days (see PruneInspirationPhotos), the client can
 * remove it from My Bookings, and "Book this look again" copies it onto the
 * next booking.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointment_inspiration_photos', function (Blueprint $table) {
            $table->string('kind', 12)->default('inspiration')->after('appointment_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('look_saved_at')->nullable()->after('inspiration_notes');
            $table->index(['customer_id', 'look_saved_at'], 'appointments_customer_look_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_customer_look_idx');
            $table->dropColumn('look_saved_at');
        });

        Schema::table('appointment_inspiration_photos', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
