<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // Card payment device already in use (e.g. Yoco) — tests the
            // "coexist with what they already paid for" positioning claim.
            $table->string('card_payment_device')->nullable()->after('balance_tracking_methods');

            // POPIA consent — required for collecting contact/financial signals.
            $table->timestamp('privacy_consented_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropColumn(['card_payment_device', 'privacy_consented_at']);
        });
    }
};
