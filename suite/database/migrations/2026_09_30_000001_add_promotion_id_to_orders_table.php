<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // orders.discount_amount already existed (unused until now — see
        // CreateOrder, which always wrote 0). This is the missing link back
        // to which Promotion produced that discount, so used_count can be
        // incremented exactly once per order and an admin can see which
        // code was redeemed on a given order.
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('discount_amount')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
        });
    }
};
