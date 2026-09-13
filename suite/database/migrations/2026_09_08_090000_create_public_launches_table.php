<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_launches', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->json('benefits')->nullable();
            $table->timestamp('launch_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('qa_enabled')->default(true);
            $table->timestamps();
        });

        // Bootstrap the Founding 20 coming-soon page so it's live the moment this deploys.
        DB::table('public_launches')->insert([
            'key'        => 'founding-20',
            'title'      => 'Founding 20',
            'tagline'    => 'Twenty South African businesses. Three months free. No setup fee.',
            'benefits'   => json_encode([
                '3 months on the full platform, free, no setup fee',
                'A curated spot, not first-come — we build around your actual workflow',
                'Direct input into what gets built next',
                'R200/month after, only if you stay — opt-in, no lock-in',
            ]),
            'launch_at'  => null,
            'is_active'  => true,
            'qa_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('public_launches');
    }
};
