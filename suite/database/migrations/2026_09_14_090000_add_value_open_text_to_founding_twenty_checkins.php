<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('founding_twenty_checkins', function (Blueprint $table) {
            // Companion to value_rating (already on this table): "what would make
            // this worth R200/month" is another question that's more honestly
            // answered after using the product than at application time — moved
            // here alongside continuation_driver/churn_driver, same reasoning.
            $table->text('value_open_text')->nullable()->after('value_rating');
        });
    }

    public function down(): void
    {
        Schema::table('founding_twenty_checkins', function (Blueprint $table) {
            $table->dropColumn('value_open_text');
        });
    }
};
