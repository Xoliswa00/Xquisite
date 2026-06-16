<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPlanInstallment extends Model
{
    protected $fillable = [
        'payment_plan_id', 'installment_number', 'label',
        'amount', 'due_date', 'paid_at', 'payment_method',
        'reference', 'status', 'notes',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function plan() { return $this->belongsTo(PaymentPlan::class, 'payment_plan_id'); }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function markPaid(string $method = 'cash', ?string $reference = null): void
    {
        $this->update([
            'status'         => 'paid',
            'paid_at'        => now(),
            'payment_method' => $method,
            'reference'      => $reference,
        ]);

        // Update running total on parent plan
        $plan = $this->plan;
        $totalPaid = $plan->installments()->where('status', 'paid')->sum('amount');
        $newStatus = $totalPaid >= $plan->total_amount ? 'completed' : 'active';
        $plan->update(['amount_paid' => $totalPaid, 'status' => $newStatus]);

        // When layby is fully paid, release reserved stock
        if ($newStatus === 'completed' && $plan->type === 'layby') {
            $plan->plannable?->markLaybyComplete();
        }

        // When event deposit is paid, confirm the appointment
        if ($this->installment_number === 1 && $plan->type === 'event_deposit') {
            $plan->plannable?->update(['status' => 'confirmed']);
        }
    }
}
