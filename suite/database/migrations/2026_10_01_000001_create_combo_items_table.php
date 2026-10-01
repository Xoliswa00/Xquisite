<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Restores product support to combos — service_combo_items originally
     * had a product_id column (2026_06_12_000001), then
     * 2026_06_13_023252_rewire_service_combo_items_to_services dropped it
     * entirely in favour of service_id only. A fresh table rather than
     * altering the old one: the old table's primary key is the composite
     * (service_combo_id, service_id) pair, and neither service_id nor
     * product_id can be part of a primary key once both become nullable
     * (a PK column is implicitly NOT NULL) — dropping/rebuilding a
     * composite primary key is also one of the operations that behaves
     * very differently between MySQL and SQLite (the project's test
     * suite), so a clean CREATE + copy + DROP sidesteps that entirely
     * rather than fighting it, same reasoning as the variant migrations
     * created fresh tables instead of restructuring products/1.
     *
     * Exactly one of service_id/product_id is set per row — enforced in
     * ServiceCombo::services()/products(), not a DB CHECK constraint (see
     * the applies_to enum gotcha from the promo-code work: a CHECK/ENUM
     * that only gets added on one driver silently diverges from the
     * other in tests).
     */
    public function up(): void
    {
        Schema::create('combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_combo_id')->constrained('service_combos')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->index('service_combo_id');
        });

        if (Schema::hasTable('service_combo_items')) {
            DB::table('service_combo_items')->orderBy('service_combo_id')->chunk(200, function ($rows) {
                $now = now();
                DB::table('combo_items')->insert(
                    $rows->map(fn ($row) => [
                        'service_combo_id' => $row->service_combo_id,
                        'service_id'       => $row->service_id,
                        'product_id'       => null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ])->all()
                );
            });

            Schema::dropIfExists('service_combo_items');
        }
    }

    public function down(): void
    {
        Schema::create('service_combo_items', function (Blueprint $table) {
            $table->foreignId('service_combo_id')->constrained('service_combos')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->primary(['service_combo_id', 'service_id']);
        });

        DB::table('combo_items')->whereNotNull('service_id')->orderBy('service_combo_id')->chunk(200, function ($rows) {
            DB::table('service_combo_items')->insert(
                $rows->map(fn ($row) => [
                    'service_combo_id' => $row->service_combo_id,
                    'service_id'       => $row->service_id,
                ])->all()
            );
        });

        Schema::dropIfExists('combo_items');
    }
};
