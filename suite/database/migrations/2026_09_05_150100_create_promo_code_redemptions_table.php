<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->restrictOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('founding_twenty_application_id')->nullable()
                ->constrained('founding_twenty_applications')->nullOnDelete();
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();

            // Snapshot of the code's definition at redemption time, so later edits
            // to the code don't retroactively change historical reporting.
            $table->string('discount_type');
            $table->decimal('discount_value', 8, 2);

            // The actual rand value this redemption represents — what we track for
            // "how much have we given away" reporting.
            $table->decimal('financial_value', 10, 2);

            $table->text('notes')->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->index('redeemed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_redemptions');
    }
};
