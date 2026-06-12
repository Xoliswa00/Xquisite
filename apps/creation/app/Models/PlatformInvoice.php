<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PlatformInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_number',
        'plan',
        'amount',
        'status',
        'due_date',
        'billing_period_start',
        'billing_period_end',
        'paid_at',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'due_date'             => 'date',
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
        'paid_at'              => 'datetime',
        'amount'               => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) return 0;
        return $this->due_date->diffInDays(now());
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid'      => ['label' => 'Paid',      'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
            'unpaid'    => ['label' => 'Unpaid',    'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
            'overdue'   => ['label' => 'Overdue',   'class' => 'bg-rose-100 text-rose-700 border-rose-200'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-slate-100 text-slate-500 border-slate-200'],
            default     => ['label' => ucfirst($this->status), 'class' => 'bg-slate-100 text-slate-500 border-slate-200'],
        };
    }

    public static function generateNumber(): string
    {
        $year  = now()->format('Y');
        $month = now()->format('m');
        $last  = static::whereYear('created_at', $year)->whereMonth('created_at', $month)->max('id') ?? 0;
        return 'PI-' . $year . $month . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
