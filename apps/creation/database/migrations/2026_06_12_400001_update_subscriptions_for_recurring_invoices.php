<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('client_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('client_id');
            $table->string('frequency')->default('monthly')->after('invoice_id'); // monthly, quarterly, yearly
            $table->date('next_invoice_date')->nullable()->after('frequency');
            $table->boolean('active')->default(true)->after('next_invoice_date');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['company_id', 'client_id', 'invoice_id', 'frequency', 'next_invoice_date', 'active']);
        });
    }
};
