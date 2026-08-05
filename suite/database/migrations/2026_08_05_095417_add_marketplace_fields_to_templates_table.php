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
        Schema::table('templates', function (Blueprint $table) {
            // Catalog / provenance
            $table->string('version', 20)->default('1.0.0')->after('blade_view');
            $table->string('author', 100)->default('Xquisite')->after('version');
            $table->json('changelog')->nullable()->after('author'); // [{version, date, notes}, ...], newest first
            $table->json('modules_supported')->nullable()->after('changelog'); // PlatformModule keys this template pairs well with
            $table->boolean('supports_theme_toggle')->default(true)->after('modules_supported'); // false only for permanently-dark templates

            // Automated + editorial scores (0-100, null = not yet scored)
            $table->unsignedTinyInteger('speed_score')->nullable()->after('supports_theme_toggle');
            $table->unsignedTinyInteger('seo_score')->nullable()->after('speed_score');
            $table->unsignedTinyInteger('accessibility_score')->nullable()->after('seo_score');
            $table->timestamp('scores_checked_at')->nullable()->after('accessibility_score');

            // Ratings — computed average from approved template_reviews, plus an
            // editorial override an admin can set (e.g. to seed credibility before
            // real reviews exist). Display logic: override wins if set.
            $table->decimal('rating_override', 3, 2)->nullable()->after('scores_checked_at');
            $table->decimal('rating_avg', 3, 2)->nullable()->after('rating_override');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_avg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn([
                'version', 'author', 'changelog', 'modules_supported', 'supports_theme_toggle',
                'speed_score', 'seo_score', 'accessibility_score', 'scores_checked_at',
                'rating_override', 'rating_avg', 'rating_count',
            ]);
        });
    }
};
