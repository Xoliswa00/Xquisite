<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('founding_twenty_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('founding_twenty_application_id')->constrained()->cascadeOnDelete();
            $table->enum('checkin_type', ['30_day', '60_day', '90_day']);

            // Same bucketed metrics as the intake questionnaire, for a like-for-like
            // before/after comparison rather than free-text answers that can't be diffed.
            $table->string('monthly_appointments')->nullable();
            $table->string('no_shows_per_month')->nullable();
            $table->string('avg_appointment_value')->nullable();
            $table->string('hours_booking_admin')->nullable();
            $table->string('hours_availability_messages')->nullable();
            $table->string('hours_manual_reminders')->nullable();

            $table->unsignedTinyInteger('value_rating')->nullable();
            $table->string('continuation_likelihood')->nullable();
            $table->text('biggest_change')->nullable();
            $table->boolean('would_recommend')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['founding_twenty_application_id', 'checkin_type'], 'f20_checkins_application_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('founding_twenty_checkins');
    }
};
