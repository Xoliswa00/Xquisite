<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('module'); // matches keys in config/modules.php
            $table->boolean('is_active')->default(true);
            $table->decimal('price_override', 8, 2)->nullable(); // null = use config price
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('billing_subscription_id')->nullable(); // ID from billing app
            $table->timestamps();

            $table->unique(['tenant_id', 'module']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('subdomain')->nullable()->unique()->after('slug');
            $table->string('custom_domain')->nullable()->unique()->after('subdomain');
            $table->boolean('custom_domain_verified')->default(false)->after('custom_domain');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['subdomain', 'custom_domain', 'custom_domain_verified']);
        });

        Schema::dropIfExists('tenant_modules');
    }
};
