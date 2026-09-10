<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_photos', function (Blueprint $table) {
            $table->string('disk', 20)->default('public')->after('path');
            $table->string('path_web')->nullable()->after('disk');
            $table->string('path_thumb')->nullable()->after('path_web');
            $table->unsignedSmallInteger('width')->nullable()->after('path_thumb');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
            $table->string('alt_text', 160)->nullable()->after('height');
            $table->timestamp('hidden_at')->nullable()->after('is_primary');
            $table->string('hidden_reason', 255)->nullable()->after('hidden_at');

            // Public portal reads "visible photos for this service" on every booking page hit.
            $table->index(['service_id', 'hidden_at']);
        });

        Schema::create('photo_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_photo_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('reason', 500)->nullable();
            $table->string('reporter_ip', 45)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['service_photo_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_reports');

        Schema::table('service_photos', function (Blueprint $table) {
            $table->dropIndex(['service_id', 'hidden_at']);
            $table->dropColumn([
                'disk', 'path_web', 'path_thumb', 'width', 'height',
                'alt_text', 'hidden_at', 'hidden_reason',
            ]);
        });
    }
};
