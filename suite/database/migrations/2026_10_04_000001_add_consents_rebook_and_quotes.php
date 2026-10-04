<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Three follow-ups to inspiration photos / saved looks:
 *
 * 1. customer_consents: an append-only record of what each client was told
 *    or agreed to, when and how (POPIA). Scopes: look_saved (notified when
 *    staff save a look, withdrawn when the client removes it), look_showcase
 *    (client opts in to the business sharing a look), rebook_reminders
 *    (client opts out of "time to rebook" messages).
 *    appointments.look_showcase_at is the current-state shortcut for reads.
 *
 * 2. Rebook reminders: services.rebook_after_days (blank = off),
 *    appointments.rebook_reminded_at (sent or deliberately skipped),
 *    customers.rebook_reminders_opt_out_at.
 *
 * 3. Quotes from inspiration photos: services.requires_quote, and the quote
 *    on the appointment (requested -> sent -> accepted | declined).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scope', 30);
            $table->string('action', 20);
            $table->string('via', 20);
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'scope'], 'customer_consents_scope_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('look_showcase_at')->nullable()->after('look_removed_at');
            $table->timestamp('rebook_reminded_at')->nullable()->after('look_showcase_at');
            $table->string('quote_status', 12)->nullable()->after('rebook_reminded_at');
            $table->decimal('quoted_price', 10, 2)->nullable()->after('quote_status');
            $table->unsignedInteger('quoted_duration_minutes')->nullable()->after('quoted_price');
            $table->text('quote_note')->nullable()->after('quoted_duration_minutes');
            $table->timestamp('quote_sent_at')->nullable()->after('quote_note');
            $table->timestamp('quote_responded_at')->nullable()->after('quote_sent_at');

            // The daily rebook job scans completed, not-yet-reminded bookings.
            $table->index(['status', 'rebook_reminded_at'], 'appointments_rebook_scan_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unsignedSmallInteger('rebook_after_days')->nullable()->after('accepts_inspiration_photos');
            $table->boolean('requires_quote')->default(false)->after('rebook_after_days');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('rebook_reminders_opt_out_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('rebook_reminders_opt_out_at');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['rebook_after_days', 'requires_quote']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_rebook_scan_idx');
            $table->dropColumn([
                'look_showcase_at', 'rebook_reminded_at', 'quote_status', 'quoted_price',
                'quoted_duration_minutes', 'quote_note', 'quote_sent_at', 'quote_responded_at',
            ]);
        });

        Schema::dropIfExists('customer_consents');
    }
};
