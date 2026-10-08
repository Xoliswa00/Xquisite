<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A customer's login setup link is created by staff on purpose, lasts a short
 * time and can be cancelled. The version is part of the signed link, so raising
 * it (new link, cancel, or use) kills every earlier link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedInteger('setup_link_version')->default(0);
            $table->timestamp('setup_link_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['setup_link_version', 'setup_link_expires_at']);
        });
    }
};
