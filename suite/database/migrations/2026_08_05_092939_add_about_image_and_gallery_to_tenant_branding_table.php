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
        Schema::table('tenant_branding', function (Blueprint $table) {
            $table->string('about_image_url')->nullable()->after('hero_image_url');
            $table->json('gallery_images')->nullable()->after('about_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_branding', function (Blueprint $table) {
            $table->dropColumn(['about_image_url', 'gallery_images']);
        });
    }
};
