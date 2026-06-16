<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plannable_type')->nullable();
            $table->unsignedBigInteger('plannable_id')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('title');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('cancellation_fee', 10, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled', 'defaulted'])->default('active');
            $table->enum('type', ['layby', 'event_deposit', 'quote_deposit', 'custom'])->default('custom');
            $table->text('notes')->nullable();
            $table->index(['plannable_type', 'plannable_id']);
            $table->timestamps();
        });

        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('installment_number');
            $table->string('label');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'waived'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('title');
            $table->json('line_items');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('deposit_percentage', 5, 2)->default(50);
            $table->enum('status', ['draft', 'sent', 'accepted', 'declined', 'expired', 'converted'])->default('draft');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->string('client_email')->nullable();
            $table->foreignId('payment_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('converted_to_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('payment_plan_installments');
        Schema::dropIfExists('payment_plans');
    }
};
