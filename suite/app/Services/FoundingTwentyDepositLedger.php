<?php

namespace App\Services;

use App\Models\FoundingTwentyApplication;
use App\Models\FoundingTwentyDepositEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Writes and reads the Founding 20 deposit journal. The dashboard, the deposits
 * page and the CSV all read from here, so the numbers cannot disagree.
 *
 *   received  Dr Bank                 Cr Deposits held
 *   refunded  Dr Deposits held        Cr Bank
 *   credited  Dr Deposits held        Cr Accounts receivable (the invoice it reduced)
 *   reversal  the row it undoes, with debit and credit swapped
 */
class FoundingTwentyDepositLedger
{
    private const POSTINGS = [
        'received' => ['bank', 'deposits_held'],
        'refunded' => ['deposits_held', 'bank'],
        'credited' => ['deposits_held', 'accounts_receivable'],
    ];

    public function record(FoundingTwentyApplication $application, string $type, ?int $userId = null, ?int $invoiceId = null, ?string $reference = null, ?string $reason = null): FoundingTwentyDepositEntry
    {
        [$debit, $credit] = self::POSTINGS[$type];

        return FoundingTwentyDepositEntry::create([
            'founding_twenty_application_id' => $application->id,
            'type' => $type,
            'amount' => $application->deposit_amount,
            'debit_account' => $debit,
            'credit_account' => $credit,
            'entry_date' => now()->toDateString(),
            'reference' => $reference ?? $application->deposit_reference,
            'invoice_id' => $invoiceId,
            'reason' => $reason,
            'recorded_by' => $userId,
        ]);
    }

    /**
     * Undo an entry by writing its opposite, and put the application back as it was.
     * Returns an error message, or null on success.
     */
    public function reverse(FoundingTwentyDepositEntry $entry, string $reason, ?int $userId): ?string
    {
        return DB::transaction(function () use ($entry, $reason, $userId) {
            $entry = FoundingTwentyDepositEntry::whereKey($entry->id)->lockForUpdate()->first();

            if ($entry->type === 'reversal') {
                return 'A reversal cannot itself be reversed. Record the movement again instead.';
            }
            if ($entry->reversal()->exists()) {
                return 'This entry has already been reversed.';
            }

            $application = FoundingTwentyApplication::whereKey($entry->founding_twenty_application_id)->lockForUpdate()->first();

            switch ($entry->type) {
                case 'refunded':
                    $application->update(['deposit_refunded_at' => null, 'deposit_refund_reference' => null]);
                    break;

                case 'credited':
                    $invoice = $entry->invoice()->lockForUpdate()->first();
                    if ($invoice) {
                        if (! in_array($invoice->status, ['unpaid', 'overdue'], true)) {
                            return 'That invoice is already ' . $invoice->status . ', so its amount cannot be put back. Record a refund of the deposit instead.';
                        }
                        $invoice->update([
                            'amount' => (float) $invoice->amount + (float) $entry->amount,
                            'notes' => trim(($invoice->notes ? $invoice->notes . "\n" : '') . 'Founding 20 deposit credit of R' . number_format((float) $entry->amount, 2) . ' reversed: ' . $reason),
                        ]);
                    }
                    $application->update(['deposit_credited_at' => null, 'deposit_credit_invoice_id' => null]);
                    break;

                case 'received':
                    if ($application->isDepositSettled()) {
                        return 'This deposit has been paid back or credited. Reverse that first.';
                    }
                    $application->update(['deposit_confirmed_at' => null]);
                    break;
            }

            FoundingTwentyDepositEntry::create([
                'founding_twenty_application_id' => $entry->founding_twenty_application_id,
                'type' => 'reversal',
                'amount' => $entry->amount,
                'debit_account' => $entry->credit_account,
                'credit_account' => $entry->debit_account,
                'entry_date' => now()->toDateString(),
                'reference' => $entry->reference,
                'invoice_id' => $entry->invoice_id,
                'reverses_entry_id' => $entry->id,
                'reason' => $reason,
                'recorded_by' => $userId,
            ]);

            return null;
        });
    }

    /** Entries that still stand: not a reversal, and not undone by one. */
    private function standing(): Collection
    {
        return FoundingTwentyDepositEntry::where('type', '!=', 'reversal')->whereDoesntHave('reversal')->get();
    }

    /** @return array{received: float, refunded: float, credited: float, held: float} */
    public function totals(): array
    {
        $standing = $this->standing();

        $received = (float) $standing->where('type', 'received')->sum('amount');
        $refunded = (float) $standing->where('type', 'refunded')->sum('amount');
        $credited = (float) $standing->where('type', 'credited')->sum('amount');

        return ['received' => $received, 'refunded' => $refunded, 'credited' => $credited, 'held' => $received - $refunded - $credited];
    }

    /**
     * Applications whose own record disagrees with the journal. Should always be empty;
     * if it is not, somebody changed a deposit without going through the ledger.
     */
    public function discrepancies(): Collection
    {
        $held = $this->standing()->groupBy('founding_twenty_application_id')->map(function ($entries) {
            return (float) $entries->where('type', 'received')->sum('amount')
                - (float) $entries->whereIn('type', ['refunded', 'credited'])->sum('amount');
        });

        return FoundingTwentyApplication::whereNotNull('deposit_amount')->get()->filter(function ($a) use ($held) {
            $expected = ($a->deposit_confirmed_at && ! $a->isDepositSettled()) ? (float) $a->deposit_amount : 0.0;

            return abs($expected - (float) ($held[$a->id] ?? 0)) > 0.001;
        })->values();
    }
}
