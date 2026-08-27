<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['id_copy', 'proof_of_income', 'bank_statement', 'proof_of_residence', 'other'])->index();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_documents');
    }
};
