<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlatformInvoice extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_number', 'plan', 'amount', 'status',
        'due_date', 'billing_period_start', 'billing_period_end',
        'paid_at', 'payment_method', 'payment_reference', 'notes',
        'pop_path', 'pop_uploaded_at', 'pop_notes',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'due_date'             => 'date',
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
        'paid_at'              => 'datetime',
        'pop_uploaded_at'      => 'datetime',
    ];

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
