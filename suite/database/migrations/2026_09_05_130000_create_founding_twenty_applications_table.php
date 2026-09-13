<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('founding_twenty_applications', function (Blueprint $table) {
            $table->id();

            // Contact & business identity
            $table->string('business_name');
            $table->string('owner_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('business_type');
            $table->string('business_type_other')->nullable();
            $table->string('location')->nullable();
            $table->string('preferred_contact_method')->default('whatsapp');
            $table->string('best_contact_time')->nullable();

            // Section 1 — business profile
            $table->string('years_operating')->nullable();
            $table->string('staff_count')->nullable();
            $table->unsignedInteger('locations_count')->nullable();
            $table->string('monthly_customers')->nullable();
            $table->string('monthly_appointments')->nullable();

            // Section 2 — current operations (multi-select, stored as JSON arrays)
            $table->json('booking_methods')->nullable();
            $table->json('appointment_management_methods')->nullable();
            $table->json('customer_data_methods')->nullable();
            $table->json('payment_tracking_methods')->nullable();
            $table->json('balance_tracking_methods')->nullable();

            // Section 3 — pain frequency (1 = never, 5 = very often)
            $table->unsignedTinyInteger('pain_forgotten_appointments')->nullable();
            $table->unsignedTinyInteger('pain_late_cancellations')->nullable();
            $table->unsignedTinyInteger('pain_no_shows')->nullable();
            $table->unsignedTinyInteger('pain_double_bookings')->nullable();
            $table->unsignedTinyInteger('pain_booking_enquiry_time')->nullable();
            $table->unsignedTinyInteger('pain_staff_availability')->nullable();
            $table->unsignedTinyInteger('pain_tracking_balances')->nullable();
            $table->unsignedTinyInteger('pain_revenue_visibility')->nullable();
            $table->unsignedTinyInteger('pain_customer_data_organisation')->nullable();

            // Section 4 — quantified pain
            $table->string('no_shows_per_month')->nullable();
            $table->string('avg_appointment_value')->nullable();

            // Section 5 — time cost
            $table->string('hours_booking_admin')->nullable();
            $table->string('hours_availability_messages')->nullable();
            $table->string('hours_manual_reminders')->nullable();

            // Section 6 — current alternatives
            $table->json('adoption_barriers')->nullable();
            $table->string('adoption_barrier_other')->nullable();
            $table->text('past_solution_frustration')->nullable();

            // Section 7 — feature prioritisation
            $table->json('priority_features')->nullable();
            $table->string('top_priority_feature')->nullable();
            $table->text('automation_wishlist')->nullable();

            // Section 8 — value
            $table->unsignedTinyInteger('value_rating')->nullable();
            $table->text('value_open_text')->nullable();

            // Section 9 — willingness to pay
            $table->string('continuation_likelihood')->nullable();
            $table->text('continuation_driver')->nullable();
            $table->text('churn_driver')->nullable();

            // Section 10 — Founding 20 opt-in
            $table->boolean('wants_founding_twenty')->default(true);
            $table->boolean('willing_to_give_feedback')->default(false);

            // Scoring & review
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('tier')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'selected', 'waitlisted', 'rejected', 'converted'])
                ->default('pending')
                ->index();
            $table->string('source')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('founding_twenty_applications');
    }
};
