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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

               $table->unsignedBigInteger('platform_owner_id')->nullable();
               

                $table->boolean('is_platform_company')->default(false);

            // Core Identity
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('vat_number')->nullable();

            // Classification
            $table->enum('entity_type', [
                'sole_proprietor',
                'private_company',
                'partnership',
                'trust',
                'non_profit',
                'other'
            ])->default('private_company');

            // Contact Info
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Address
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('South Africa');

            // Financial Defaults
            $table->string('currency', 10)->default('ZAR');
            $table->boolean('vat_registered')->default(false);
            $table->decimal('default_vat_rate', 5, 2)->default(15.00);

            // Operational Status
            $table->enum('status', ['active', 'suspended', 'closed'])
                  ->default('active');
                   $table->string('slug')->unique();

            // Domain-based onboarding
            $table->string('domain')->nullable()->unique();

            // Settings
            $table->string('logo_path')->nullable();
            $table->string('timezone')->default('UTC');

            // Billing info
            $table->string('billing_email')->nullable();
            $table->string('plan')->default('basic');

             $table->string('subscription_plan')->nullable();
    $table->string('subscription_status')->default('active');
    $table->date('subscription_renewal_date')->nullable();










            $table->timestamps();
                $table->softDeletes();


            // Indexing for performance
            $table->index('registration_number');
            $table->index('vat_number');
            $table->index('status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
