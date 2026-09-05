<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            // Refundable reservation deposit — the commitment signal for
            // a free-trial offer that would otherwise collect no real intent data.
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->string('deposit_reference')->nullable();
            $table->string('deposit_pop_path')->nullable();
            $table->timestamp('deposit_submitted_at')->nullable();
            $table->timestamp('deposit_confirmed_at')->nullable();
            $table->timestamp('deposit_refunded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_applications', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_amount', 'deposit_reference', 'deposit_pop_path',
                'deposit_submitted_at', 'deposit_confirmed_at', 'deposit_refunded_at',
            ]);
        });
    }
};
