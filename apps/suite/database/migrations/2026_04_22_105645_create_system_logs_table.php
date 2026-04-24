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

$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
