<?php

namespace App\Modules\ServiceDelivery\Models;

use App\Models\BillingSetting;
use App\Models\Client;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'client_id', 'gig_id', 'service_agreement_id',
        'invoice_number', 'status', 'issue_date', 'due_date', 'payment_terms',
        'line_items', 'subtotal', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'paid_at', 'payment_method', 'payment_reference', 'notes',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'paid_at'     => 'date',
        'line_items'  => 'array',
        'subtotal'    => 'decimal:2',
        'tax_rate'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function serviceAgreement()
    {
        return $this->belongsTo(ServiceAgreement::class);
    }

    /**
     * Recompute subtotal/tax/total from line_items. Each item:
     * ['description' => ..., 'quantity' => ..., 'unit_price' => ..., 'discount_percent' => 0-100]
     * Line total = quantity * unit_price * (1 - discount_percent / 100).
     */
    public function recalculate(): void
    {
        $subtotal = collect($this->line_items)->sum(function ($item) {
            $gross = (float) $item['quantity'] * (float) $item['unit_price'];
            $discountPct = (float) ($item['discount_percent'] ?? 0);
            return round($gross * (1 - $discountPct / 100), 2);
        });

        $taxAmount = round($subtotal * ((float) $this->tax_rate / 100), 2);

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total = $subtotal + $taxAmount;
    }

    public function balanceDue(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function isOverdue(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled']) && $this->due_date->isPast();
    }

    /**
     * Continues the real invoice numbering already in use (last seen: INV01261,
     * from the Python script this replaces) rather than restarting at 1 — avoids
     * colliding with invoice numbers already sent to real clients.
     */
    public static function generateNumber(int $tenantId): string
    {
        $startAt = 1262;

        $lastNumeric = static::where('tenant_id', $tenantId)
            ->get()
            ->map(fn ($inv) => (int) preg_replace('/\D/', '', $inv->invoice_number))
            ->max();

        $next = max($startAt, ($lastNumeric ?? 0) + 1);

        return 'INV' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public static function defaultVatRate(): float
    {
        return (float) BillingSetting::get('vat_rate');
    }
}
