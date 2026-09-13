<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['free_months', 'percentage', 'fixed_amount']);
            $table->decimal('value', 8, 2); // months count | % 0-100 | rand amount, depending on type
            $table->unsignedInteger('max_redemptions')->nullable(); // null = unlimited
            $table->unsignedInteger('times_redeemed')->default(0);
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('source')->nullable(); // e.g. 'founding_twenty'
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
