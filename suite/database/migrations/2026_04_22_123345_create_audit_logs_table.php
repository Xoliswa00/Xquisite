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
        Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
         // WHO
    $table->foreignId('user_id')->nullable()->index();

    // WHAT
    $table->string('action'); 
    // e.g. user.created, invoice.updated, payment.failed

    $table->string('entity_type')->nullable(); 
    // e.g. User, Invoice, Transaction

    $table->unsignedBigInteger('entity_id')->nullable();

    // CHANGES
    $table->longText('old_values')->nullable();
    $table->longText('new_values')->nullable();

    // CONTEXT
    $table->string('request_id')->nullable()->index();
    $table->string('ip_address')->nullable();
    $table->text('url')->nullable();

    // META
    $table->json('meta')->nullable();

    $table->timestamps();

    // PERFORMANCE INDEXES
    $table->index(['entity_type', 'entity_id']);
    $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
