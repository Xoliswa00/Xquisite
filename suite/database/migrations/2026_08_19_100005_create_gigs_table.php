<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gigs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->enum('category', [
                'software_solutions', 'business_automation', 'data_intelligence',
                'digital_solutions', 'other',
            ])->default('digital_solutions');

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('discovery_notes')->nullable();

            $table->enum('status', ['lead', 'quoted', 'in_progress', 'review', 'completed', 'cancelled'])
                ->default('lead')->index();

            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->date('deadline_date')->nullable();
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->enum('invoice_status', ['not_invoiced', 'invoiced', 'paid'])->default('not_invoiced');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gigs');
    }
};
