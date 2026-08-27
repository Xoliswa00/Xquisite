<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->enum('employment_type', ['permanent', 'contract', 'self_employed', 'unemployed', 'other'])->nullable()->after('employer');
            $table->unsignedInteger('employment_months')->nullable()->after('employment_type');
            $table->decimal('monthly_expenses', 10, 2)->nullable()->after('monthly_income');
            $table->unsignedTinyInteger('number_of_occupants')->nullable()->after('monthly_expenses');
            $table->string('previous_landlord_name')->nullable()->after('number_of_occupants');
            $table->string('previous_landlord_phone')->nullable()->after('previous_landlord_name');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn([
                'employment_type', 'employment_months', 'monthly_expenses',
                'number_of_occupants', 'previous_landlord_name', 'previous_landlord_phone',
            ]);
        });
    }
};
