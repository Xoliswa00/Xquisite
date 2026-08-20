<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('gig_id')->nullable()->after('customer_id')->constrained('gigs')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->after('gig_id')->constrained('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gig_id');
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
