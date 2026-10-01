<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // A real FK, not copying a photo's URL string into the
            // existing image_url column — if a linked photo is later
            // deleted from the product's gallery, this clears
            // automatically (nullOnDelete) and ProductVariant::
            // effectiveImageUrl() falls back cleanly to the parent
            // product's cover, instead of the variant silently pointing
            // at a 404'd file with nothing to detect it. image_url
            // itself is untouched — still available as a manual-paste
            // override, now second priority behind this link.
            $table->foreignId('product_photo_id')->nullable()->after('image_url')
                ->constrained('product_photos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_photo_id');
        });
    }
};
