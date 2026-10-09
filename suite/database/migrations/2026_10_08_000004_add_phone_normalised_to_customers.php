<?php

use App\Modules\Booking\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customers can sign in with a cell number. Numbers are stored however they
 * were typed ("082 123 4567", "+27821234567"), so matching one meant loading
 * every customer of the business and comparing in PHP on each sign-in attempt.
 * This keeps the 0-prefixed 10-digit form alongside, indexed per business.
 * The Customer model fills it on every save.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone_normalised', 20)->nullable();
            $table->index(['tenant_id', 'phone_normalised'], 'customers_tenant_phone_idx');
        });

        DB::table('customers')->whereNotNull('phone')->orderBy('id')->select('id', 'phone')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    if ($normalised = Customer::normalisePhone($row->phone)) {
                        DB::table('customers')->where('id', $row->id)->update(['phone_normalised' => $normalised]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_tenant_phone_idx');
            $table->dropColumn('phone_normalised');
        });
    }
};
