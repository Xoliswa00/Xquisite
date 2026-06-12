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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
           $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('client_id')->constrained()->cascadeOnDelete();
    $table->string('quote_number')->unique();
$table->enum('status', [
    'draft',
    'submitted',
    'under_review',
    'sent',
    'viewed',
    'approved',
    'rejected',
    'invoiced'
])->default('draft');
$table->enum('source', ['client', 'internal'])->default('internal');
$table->text('internal_notes')->nullable();
$table->text('client_notes')->nullable();

$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->decimal('subtotal', 15, 2)->default(0);
    $table->decimal('vat', 15, 2)->default(0);
    $table->decimal('total', 15, 2)->default(0);
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
