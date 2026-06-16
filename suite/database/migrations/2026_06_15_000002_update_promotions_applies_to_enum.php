<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE promotions MODIFY applies_to ENUM('all', 'services', 'products') NOT NULL DEFAULT 'all'");

        // Re-map any legacy 'combos' rows to 'services' (promo codes don't apply to combos)
        DB::table('promotions')->where('applies_to', 'combos')->update(['applies_to' => 'services']);
    }

    public function down(): void
    {
        DB::table('promotions')->where('applies_to', 'services')->update(['applies_to' => 'all']);
        DB::statement("ALTER TABLE promotions MODIFY applies_to ENUM('all', 'products', 'combos') NOT NULL DEFAULT 'all'");
    }
};
