<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->text('message');
            $table->longText('context')->nullable();
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('request_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('url')->nullable();

            // Status tracker
            $table->enum('status', ['new', 'acknowledged', 'in_progress', 'resolved'])->default('new')->index();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();

            // Source — always 'billing' here; used when pulled into suite central view
            $table->string('source')->default('billing')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
