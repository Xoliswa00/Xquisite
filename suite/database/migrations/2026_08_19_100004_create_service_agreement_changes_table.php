<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_agreement_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('service_agreement_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // 'YYYY-MM'
            $table->string('description');
            $table->unsignedInteger('minutes_used');
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_agreement_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_agreement_changes');
    }
};
