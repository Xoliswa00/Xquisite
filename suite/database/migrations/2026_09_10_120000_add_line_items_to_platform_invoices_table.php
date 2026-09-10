<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded so a mid-run failure in the backfill below leaves the migration
        // re-runnable rather than dying on "column already exists".
        if (! Schema::hasColumn('platform_invoices', 'line_items')) {
            Schema::table('platform_invoices', function (Blueprint $table) {
                $table->json('line_items')->nullable()->after('plan');
                $table->decimal('subtotal', 10, 2)->nullable()->after('line_items');
                $table->decimal('vat_amount', 10, 2)->nullable()->after('subtotal');
                $table->decimal('vat_rate', 5, 2)->nullable()->after('vat_amount');
            });
        }

        // Backfill so historical invoices still render under the line-item
        // template: one synthetic line equal to the frozen amount, no VAT.
        // subtotal + vat_amount stays equal to amount. Idempotent (whereNull).
        DB::table('platform_invoices')->whereNull('line_items')->orderBy('id')
            ->lazyById(200)->each(function ($row) {
                DB::table('platform_invoices')->where('id', $row->id)->update([
                    'line_items' => json_encode([[
                        'key'        => 'subscription',
                        'name'       => 'Xquisite platform subscription',
                        'quantity'   => 1,
                        'unit_price' => (float) $row->amount,
                        'amount'     => (float) $row->amount,
                    ]]),
                    'subtotal'   => $row->amount,
                    'vat_amount' => 0,
                    'vat_rate'   => 0,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('platform_invoices', function (Blueprint $table) {
            $table->dropColumn(['line_items', 'subtotal', 'vat_amount', 'vat_rate']);
        });
    }
};
