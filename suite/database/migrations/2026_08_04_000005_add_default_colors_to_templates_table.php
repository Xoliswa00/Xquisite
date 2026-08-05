<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each template ships with its own signature palette (set here) so the
     * catalog shows visually distinct designs by default — tenant branding
     * colors, when set, always take priority over these.
     */
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->string('default_primary_color', 7)->nullable()->after('price');
            $table->string('default_secondary_color', 7)->nullable()->after('default_primary_color');
            $table->string('default_accent_color', 7)->nullable()->after('default_secondary_color');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn(['default_primary_color', 'default_secondary_color', 'default_accent_color']);
        });
    }
};
