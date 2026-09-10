<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlatformInvoice extends Model
{
    use Auditable;

    protected $fillable = [
        'tenant_id', 'invoice_number', 'plan', 'amount',
        'line_items', 'subtotal', 'vat_amount', 'vat_rate',
        'status', 'due_date', 'billing_period_start', 'billing_period_end',
        'paid_at', 'payment_method', 'payment_reference', 'notes',
        'pop_path', 'pop_uploaded_at', 'pop_notes',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'subtotal'             => 'decimal:2',
        'vat_amount'           => 'decimal:2',
        'vat_rate'             => 'decimal:2',
        'line_items'           => 'array',
        'due_date'             => 'date',
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
        'paid_at'              => 'datetime',
        'pop_uploaded_at'      => 'datetime',
    ];

    /**
     * A tax invoice only when it carries a VAT line AND the issuer has a VAT
     * number to put on it — a "Tax Invoice" without one is not compliant.
     */
    public function isTaxInvoice(): bool
    {
        return (float) $this->vat_amount > 0 && (bool) BillingSetting::get('company_vat');
    }

    /** The VAT rate as it stood when the invoice was issued, e.g. "15" or "15.5". */
    public function vatRateLabel(): string
    {
        return rtrim(rtrim(number_format((float) $this->vat_rate, 2), '0'), '.');
    }

    /**
     * The line-item shape used when an invoice predates the `line_items` column:
     * a single subscription line equal to the frozen amount.
     *
     * @return array<int, array{key: string, name: string, quantity: int, unit_price: float, amount: float}>
     */
    public function fallbackLineItems(): array
    {
        return [[
            'key'        => 'subscription',
            'name'       => 'Xquisite platform subscription',
            'quantity'   => 1,
            'unit_price' => (float) $this->amount,
            'amount'     => (float) $this->amount,
        ]];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function hasPop(): bool
    {
        return (bool) $this->pop_path;
    }

    public function isAwaitingConfirmation(): bool
    {
        return $this->hasPop() && in_array($this->status, ['unpaid', 'overdue']);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!in_array($this->status, ['unpaid', 'overdue'])) return 0;
        return max(0, now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false) * -1);
    }

    /** Signed days until due_date: positive = days remaining, 0 = due today, negative = days overdue. */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->startOfDay()->diffInDays($this->due_date->copy()->startOfDay(), false);
    }

    public function getStatusBadgeAttribute(): array
    {
        if ($this->isAwaitingConfirmation()) {
            return ['label' => 'POP Submitted', 'class' => 'bg-[#0078D4]/20 text-[#0078D4] border-[#0078D4]/30'];
        }

        return match ($this->status) {
            'paid'      => ['label' => 'Paid',      'class' => 'bg-emerald-900/40 text-emerald-300 border-emerald-700'],
            'overdue'   => ['label' => 'Overdue',   'class' => 'bg-red-900/40 text-red-300 border-red-700'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-slate-700 text-slate-400 border-slate-600'],
            default     => ['label' => 'Unpaid',    'class' => 'bg-amber-900/40 text-amber-300 border-amber-700'],
        };
    }

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'PI-' . now()->format('Ym') . '-';
            $last = static::where('invoice_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->value('invoice_number');
            $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
            return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
