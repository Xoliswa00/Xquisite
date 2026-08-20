<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();

            $table->enum('category', [
                'software_solutions', 'business_automation', 'data_intelligence',
                'digital_solutions', 'ongoing_support', 'other',
            ])->default('other');

            $table->text('description');
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();
            $table->string('ip_address')->nullable();

            $table->enum('status', ['new', 'reviewed', 'converted', 'dismissed'])->default('new')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('converted_gig_id')->nullable()->constrained('gigs')->nullOnDelete();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
