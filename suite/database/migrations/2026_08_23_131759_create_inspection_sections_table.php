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
        Schema::create('inspection_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Kitchen", "Bedroom 1", "Bathroom 2"
            $table->enum('condition', ['good', 'fair', 'poor', 'damaged'])->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'inspection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_sections');
    }
};
