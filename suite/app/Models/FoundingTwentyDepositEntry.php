<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * One balanced line in the deposit journal. Append-only: once written it cannot be
 * changed or removed, so what the accountant sees is what happened. Corrections are
 * new "reversal" rows that swap the debit and credit of the row they undo.
 */
class FoundingTwentyDepositEntry extends Model
{
    use Auditable;

    public const ACCOUNTS = [
        'bank' => 'Bank',
        'deposits_held' => 'Deposits held (owed back)',
        'accounts_receivable' => 'Accounts receivable (invoices)',
    ];

    protected $fillable = [
        'founding_twenty_application_id', 'type', 'amount', 'debit_account', 'credit_account',
        'entry_date', 'reference', 'invoice_id', 'reverses_entry_id', 'reason', 'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Deposit journal entries cannot be edited. Record a reversal instead.'));
        static::deleting(fn () => throw new \LogicException('Deposit journal entries cannot be deleted. Record a reversal instead.'));
    }

    public function application()
    {
        return $this->belongsTo(FoundingTwentyApplication::class, 'founding_twenty_application_id');
    }

    public function invoice()
    {
        return $this->belongsTo(PlatformInvoice::class, 'invoice_id');
    }

    public function reversedEntry()
    {
        return $this->belongsTo(self::class, 'reverses_entry_id');
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reverses_entry_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function description(): string
    {
        return match ($this->type) {
            'received' => 'Deposit received',
            'refunded' => 'Deposit paid back',
            'credited' => 'Deposit credited to invoice',
            'reversal' => 'Reversal',
        };
    }
}
