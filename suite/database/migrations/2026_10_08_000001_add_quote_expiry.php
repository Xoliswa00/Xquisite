<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Quotes from photos get a deadline, so an unanswered quote can't hold a slot
 * forever: tenants.quote_expiry_hours (owner setting, default 48) and, per
 * quote, appointments.quote_expires_at + quote_reminded_at (one "expires
 * soon" nudge). See AppointmentQuoteService::deadlineFor().
 *
 * Quotes already sent get a fresh 48 hours from now (capped at the
 * appointment time), so nobody's open quote expires the moment this deploys.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedSmallInteger('quote_expiry_hours')->default(48);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('quote_expires_at')->nullable()->after('quote_responded_at');
            $table->timestamp('quote_reminded_at')->nullable()->after('quote_expires_at');
            // The expiry job scans open quotes by deadline.
            $table->index(['quote_status', 'quote_expires_at'], 'appointments_quote_expiry_idx');
        });

        $deadline = now()->addHours(48);
        DB::table('appointments')->where('quote_status', 'sent')->whereNull('quote_expires_at')
            ->orderBy('id')->each(function ($row) use ($deadline) {
                $start = \Illuminate\Support\Carbon::parse($row->scheduled_at);
                DB::table('appointments')->where('id', $row->id)
                    ->update(['quote_expires_at' => $start->lt($deadline) ? $start : $deadline]);
            });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_quote_expiry_idx');
            $table->dropColumn(['quote_expires_at', 'quote_reminded_at']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('quote_expiry_hours');
        });
    }
};
