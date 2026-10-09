<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * customers.email was unique across the whole platform, so a client of two
 * businesses could not have a login at the second one (and the error told them
 * the address was known somewhere else). Every customer query is already scoped
 * to the business, so the address only needs to be unique within one.
 */
return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('customers'));

        // Found by their columns, not their names: some databases were not built
        // from these migrations and already carry the per-business rule under
        // another name (customers_tenant_id_email_unique), or have no global one.
        $global      = $indexes->first(fn ($index) => $index['unique'] && $index['columns'] === ['email']);
        $perBusiness = $indexes->first(fn ($index) => $index['unique'] && $index['columns'] === ['tenant_id', 'email']);

        Schema::table('customers', function (Blueprint $table) use ($global, $perBusiness) {
            // New rule first: at no point is the column without a unique index.
            if (! $perBusiness) {
                $table->unique(['tenant_id', 'email'], 'customers_tenant_email_unique');
            }

            if ($global) {
                $table->dropUnique($global['name']);
            }
        });
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('customers'));

        // Check before touching anything, so a refusal leaves the table as it was.
        $duplicates = DB::table('customers')->whereNotNull('email')
            ->select('email')->groupBy('email')->havingRaw('COUNT(*) > 1')->limit(1)->exists();

        if ($duplicates) {
            throw new RuntimeException('Cannot restore the platform-wide unique email: the same address now exists at more than one business. Resolve those rows first.');
        }

        $hasGlobal = $indexes->contains(fn ($index) => $index['unique'] && $index['columns'] === ['email']);
        $ours      = $indexes->contains(fn ($index) => $index['name'] === 'customers_tenant_email_unique');

        Schema::table('customers', function (Blueprint $table) use ($hasGlobal, $ours) {
            if (! $hasGlobal) {
                $table->unique('email', 'customers_email_unique');
            }

            // Only the index this migration added; one that was already there stays.
            if ($ours) {
                $table->dropUnique('customers_tenant_email_unique');
            }
        });
    }
};
