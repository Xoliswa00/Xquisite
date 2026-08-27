<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_request_contractor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contractor_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['maintenance_request_id', 'contractor_id'], 'maint_req_contractor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_request_contractor');
    }
};
