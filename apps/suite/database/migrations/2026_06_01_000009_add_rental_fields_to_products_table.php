<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_rentable')->default(false)->after('is_active');
            $table->decimal('rental_rate', 8, 2)->nullable()->after('is_rentable');
            $table->unsignedSmallInteger('total_units')->nullable()->after('rental_rate');
            $table->enum('condition', ['excellent', 'good', 'fair', 'needs_repair'])->default('excellent')->after('total_units');
        });

        Schema::create('rental_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('reference')->unique();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('rental_rate', 8, 2);
            $table->date('event_date');
            $table->date('return_due_at');
            $table->timestamp('returned_at')->nullable();
            $table->enum('condition_on_return', ['excellent', 'good', 'fair', 'damaged'])->nullable();
            $table->enum('status', ['reserved', 'out', 'returned', 'overdue', 'damaged'])->default('reserved');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_orders');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_rentable', 'rental_rate', 'total_units', 'condition']);
        });
    }
};
