<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_services', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('category');        // onboarding | training | support | custom
            $table->enum('billing_type', ['once_off', 'recurring'])->default('once_off');
            $table->decimal('price', 8, 2)->nullable(); // null = quoted per client
            $table->string('price_label')->nullable();  // e.g. "from R750" or "Custom quote"
            $table->string('icon')->default('wrench');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_requestable')->default(true); // clients can self-request
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenant_service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_service_id')->constrained()->restrictOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['requested', 'quoted', 'approved', 'in_progress', 'delivered', 'cancelled'])
                  ->default('requested');
            $table->decimal('quoted_price', 8, 2)->nullable();
            $table->text('client_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->date('requested_date')->nullable();
            $table->date('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_service_orders');
        Schema::dropIfExists('platform_services');
    }
};
