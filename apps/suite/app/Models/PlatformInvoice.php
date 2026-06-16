<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PlatformInvoice extends Model
{
    protected $fillable = [
        'tenant_id', 'invoice_number', 'plan', 'amount', 'status',
        'due_date', 'billing_period_start', 'billing_period_end',
        'paid_at', 'payment_method', 'payment_reference', 'notes',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'due_date'             => 'date',
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
        'paid_at'              => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!in_array($this->status, ['unpaid', 'overdue'])) return 0;
        return max(0, now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false) * -1);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid'      => ['label' => 'Paid',      'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
            'overdue'   => ['label' => 'Overdue',   'class' => 'bg-red-100 text-red-700 border-red-200'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
            default     => ['label' => 'Unpaid',    'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
        };
    }

    public static function generateNumber(): string
    {
        $prefix = 'PI-' . now()->format('Ym') . '-';
        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
