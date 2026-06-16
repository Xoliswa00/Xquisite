<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedSmallInteger('headcount')->nullable()->after('notes');
            $table->string('venue', 255)->nullable()->after('headcount');
            $table->string('event_type', 50)->nullable()->after('venue');
            $table->text('dietary_notes')->nullable()->after('event_type');
            $table->text('theme_notes')->nullable()->after('dietary_notes');
            $table->datetime('setup_at')->nullable()->after('theme_notes');
            $table->datetime('breakdown_at')->nullable()->after('setup_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['headcount','venue','event_type','dietary_notes','theme_notes','setup_at','breakdown_at']);
        });
    }
};
