<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('platform_invoices', function (Blueprint $table) {
            $table->string('pop_path')->nullable()->after('notes');
            $table->timestamp('pop_uploaded_at')->nullable()->after('pop_path');
            $table->text('pop_notes')->nullable()->after('pop_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('platform_invoices', function (Blueprint $table) {
            $table->dropColumn(['pop_path', 'pop_uploaded_at', 'pop_notes']);
        });
    }
};
