<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gig_time_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('gig_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('minutes');
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('logged_at');
            $table->timestamps();

            $table->index(['gig_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gig_time_entries');
    }
};
