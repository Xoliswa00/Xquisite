<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    protected $fillable = [
        'plannable_type', 'plannable_id', 'tenant_id', 'customer_id',
        'title', 'total_amount', 'amount_paid', 'cancellation_fee',
        'status', 'type', 'notes',
    ];

    protected $casts = [
        'total_amount'     => 'decimal:2',
        'amount_paid'      => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
    ];

    public function installments()
    {
        return $this->hasMany(PaymentPlanInstallment::class)->orderBy('installment_number');
    }

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function customer()  { return $this->belongsTo(\App\Modules\Booking\Models\Customer::class); }
    public function plannable() { return $this->morphTo(); }

    public function scopeActive(Builder $q): Builder   { return $q->where('status', 'active'); }
    public function scopeOverdue(Builder $q): Builder
    {
        return $q->active()->whereHas('installments', fn ($i) =>
            $i->where('status', 'pending')->where('due_date', '<', now()->toDateString())
        );
    }

    public function amountOutstanding(): float
    {
        return max(0, $this->total_amount - $this->amount_paid);
    }

    public function progressPercent(): int
    {
        if ($this->total_amount <= 0) return 100;
        return (int) min(100, round(($this->amount_paid / $this->total_amount) * 100));
    }

    public function nextDue(): ?PaymentPlanInstallment
    {
        return $this->installments->where('status', 'pending')->first();
    }

    /**
     * Generate an installment schedule.
     * $deposit = amount for first installment
     * $remainingInstallments = how many more after deposit (0 = deposit + one balance)
     * $intervalDays = days between instalments
     */
    public static function buildSchedule(
        float $totalAmount,
        float $depositAmount,
        int   $remainingInstallments,
        string $depositDue,
        int   $intervalDays = 30
    ): array {
        $installments = [];
        $installments[] = [
            'installment_number' => 1,
            'label'              => 'Deposit',
            'amount'             => $depositAmount,
            'due_date'           => $depositDue,
            'status'             => 'pending',
        ];

        $remaining    = $totalAmount - $depositAmount;
        $perInstalment = $remainingInstallments > 0
            ? round($remaining / $remainingInstallments, 2)
            : $remaining;

        $dueDate = \Carbon\Carbon::parse($depositDue);

        for ($i = 1; $i <= max(1, $remainingInstallments); $i++) {
            $dueDate = $dueDate->addDays($intervalDays);
            $label   = $remainingInstallments <= 1 ? 'Balance' : "Instalment {$i}";
            $installments[] = [
                'installment_number' => $i + 1,
                'label'              => $label,
                'amount'             => $i === $remainingInstallments ? $remaining - ($perInstalment * ($i - 1)) : $perInstalment,
                'due_date'           => $dueDate->toDateString(),
                'status'             => 'pending',
            ];
        }

        return $installments;
    }
}
