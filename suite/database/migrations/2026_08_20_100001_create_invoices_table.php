<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gig_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_agreement_id')->nullable()->constrained()->nullOnDelete();

            $table->string('invoice_number');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft')->index();

            $table->date('issue_date');
            $table->date('due_date');
            $table->string('payment_terms')->default('Net 15');

            // [{description, quantity, unit_price, discount_percent, line_total}]
            $table->json('line_items');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(15.00);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);

            $table->date('paid_at')->nullable();
            $table->enum('payment_method', ['eft', 'cash', 'card', 'debit_order', 'other'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
