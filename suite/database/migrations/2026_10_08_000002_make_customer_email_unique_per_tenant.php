<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasIndex('customers', 'customers_email_unique')) {
                $table->dropUnique('customers_email_unique');
            }

            $table->unique(['tenant_id', 'email'], 'customers_tenant_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_tenant_email_unique');
            // Fails if the same address now exists at two businesses; those rows
            // have to be resolved by hand before rolling back.
            $table->unique('email', 'customers_email_unique');
        });
    }
};
