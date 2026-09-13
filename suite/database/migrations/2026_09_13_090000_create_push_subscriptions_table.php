<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotificationChannels\WebPush\PushSubscription;

// Shared table for both User (staff/admin) and Customer push subscriptions —
// `subscribable` is polymorphic so either model can hold its own subscriptions
// without a separate table per notifiable type. Created at the current
// ENDPOINT_MAX_LENGTH directly (the package ships a separate follow-up
// migration to widen an older, shorter column — not needed on a fresh table).
return new class extends Migration
{
    public function up(): void
    {
        $connection = config('webpush.database_connection');
        $tableName  = config('webpush.table_name');

        Schema::connection($connection)->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('subscribable', 'push_subscriptions_subscribable_morph_idx');
            $table->string('endpoint', PushSubscription::ENDPOINT_MAX_LENGTH)
                ->charset('ascii')
                ->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $connection = config('webpush.database_connection');
        $tableName  = config('webpush.table_name');

        Schema::connection($connection)->dropIfExists($tableName);
    }
};
