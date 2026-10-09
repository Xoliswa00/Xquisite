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
        // Drop whatever unique index sits on email alone, under any name, so the
        // platform-wide rule can't quietly survive a naming difference.
        $old = collect(Schema::getIndexes('customers'))
            ->first(fn ($index) => $index['unique'] && $index['columns'] === ['email']);

        Schema::table('customers', function (Blueprint $table) use ($old) {
            // New rule first: at no point is the column without a unique index.
            $table->unique(['tenant_id', 'email'], 'customers_tenant_email_unique');

            if ($old) {
                $table->dropUnique($old['name']);
            }
        });
    }

    public function down(): void
    {
        // Check before touching anything, so a refusal leaves the table as it was.
        $duplicates = DB::table('customers')->whereNotNull('email')
            ->select('email')->groupBy('email')->havingRaw('COUNT(*) > 1')->limit(1)->exists();

        if ($duplicates) {
            throw new RuntimeException('Cannot restore the platform-wide unique email: the same address now exists at more than one business. Resolve those rows first.');
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('email', 'customers_email_unique');
            $table->dropUnique('customers_tenant_email_unique');
        });
    }
};
