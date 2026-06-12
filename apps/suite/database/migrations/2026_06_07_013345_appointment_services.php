<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    
            // 1. Remove service_id from appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            // also drop duration_minutes if you'll derive it from services
        });

        // 2. Create the pivot table
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('duration_minutes')->nullable(); // override if needed
            $table->decimal('price_at_booking', 8, 2)->nullable();  // snapshot price at time of booking
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['appointment_id', 'service_id']); // prevent duplicates
            $table->index('appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
