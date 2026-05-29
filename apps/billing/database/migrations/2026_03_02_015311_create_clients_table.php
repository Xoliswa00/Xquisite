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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('name');
    $table->string('email')->nullable();
        $table->string('contact_person')->nullable();
         $table->string('website')->nullable();

    $table->string('tax_number')->nullable();
    $table->string('vat_number')->nullable();
    $table->string('logo_path')->nullable();


    $table->string('phone')->nullable();
    $table->string('address_line_1')->nullable();
    $table->string('address_line_2')->nullable();
    $table->string('city')->nullable();
    $table->string('province')->nullable();
    $table->string('postal_code')->nullable();
    $table->string('country')->default('South Africa');
    $table->text('billing_address')->nullable();
    $table->timestamps();

    $table->unique(['company_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
