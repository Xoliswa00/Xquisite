<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An append-only journal of every movement of a Founding 20 deposit. Each row is
 * a balanced debit/credit pair. Rows are never edited or deleted; a mistake is
 * corrected by a reversing row, so the history stays intact for the accountant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('founding_twenty_deposit_entries', function (Blueprint $table) {
            $table->id();
            // Constraint and index names are set by hand: the generated ones exceed MySQL's 64 character limit.
            $table->foreignId('founding_twenty_application_id');
            $table->foreign('founding_twenty_application_id', 'f20_deposit_entries_app_fk')->references('id')->on('founding_twenty_applications')->restrictOnDelete();
            $table->string('type', 12); // received | refunded | credited | reversal
            $table->decimal('amount', 10, 2);
            $table->string('debit_account', 30);
            $table->string('credit_account', 30);
            $table->date('entry_date');
            $table->string('reference', 100)->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('platform_invoices')->nullOnDelete();
            $table->foreignId('reverses_entry_id')->nullable()->constrained('founding_twenty_deposit_entries')->restrictOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('reverses_entry_id');
            $table->index(['founding_twenty_application_id', 'type'], 'f20_deposit_entries_app_type_idx');
        });

        // Bring existing confirmed deposits into the journal.
        $rows = DB::table('founding_twenty_applications')->whereNotNull('deposit_confirmed_at')->get();
        foreach ($rows as $a) {
            $base = ['founding_twenty_application_id' => $a->id, 'amount' => $a->deposit_amount, 'reference' => $a->deposit_reference, 'reason' => 'Carried over when the journal was introduced.', 'created_at' => now(), 'updated_at' => now()];
            DB::table('founding_twenty_deposit_entries')->insert($base + ['type' => 'received', 'debit_account' => 'bank', 'credit_account' => 'deposits_held', 'entry_date' => substr($a->deposit_confirmed_at, 0, 10)]);
            if ($a->deposit_refunded_at) {
                DB::table('founding_twenty_deposit_entries')->insert($base + ['type' => 'refunded', 'debit_account' => 'deposits_held', 'credit_account' => 'bank', 'entry_date' => substr($a->deposit_refunded_at, 0, 10)]);
            }
            if ($a->deposit_credited_at ?? null) {
                DB::table('founding_twenty_deposit_entries')->insert($base + ['type' => 'credited', 'debit_account' => 'deposits_held', 'credit_account' => 'accounts_receivable', 'entry_date' => substr($a->deposit_credited_at, 0, 10), 'invoice_id' => $a->deposit_credit_invoice_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('founding_twenty_deposit_entries');
    }
};
