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
        Schema::create('company_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->dateTime('expires_at')->nullable();
             $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
             $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
        

            $table->enum('role', [
                'owner',
                'admin',
                'client_user',
                'viewer'
            ])->default('client_user');
            $table->string('token')->unique();
  
             $table->dateTime('accepted_at')->nullable();
   

             $table->index(['company_id', 'email']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_invitations');
    }
};
