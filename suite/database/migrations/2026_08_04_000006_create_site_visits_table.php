<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('path', 255)->default('/');
            $table->string('referrer', 255)->nullable();
            // sha256(ip + user agent) — never store raw IPs; lets us approximate
            // unique visitors via COUNT(DISTINCT visitor_hash) without keeping PII.
            $table->string('visitor_hash', 64);
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index(['tenant_id', 'visited_at']);
            $table->index(['tenant_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
