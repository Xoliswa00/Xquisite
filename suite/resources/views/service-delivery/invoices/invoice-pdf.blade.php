@php
    $statusVariant = match($invoice->status) {
        'paid' => 'positive',
        'overdue' => 'negative',
        default => null,
    };
@endphp
<x-document-layout
    doc-type="Invoice"
    :reference="$invoice->invoice_number"
    date-label="Issue Date"
    :date="$invoice->issue_date->format('d M Y')"
    second-date-label="Due Date"
    :second-date="$invoice->due_date->format('d M Y')"
    :status-label="ucfirst($invoice->status)"
    :status-variant="$statusVariant"
>
    <x-slot:parties>
        <td>
            <div class="label">Bill To</div>
            <div class="name">{{ $invoice->client->name }}</div>
            @if($invoice->client->email)<div class="line">{{ $invoice->client->email }}</div>@endif
            @if($invoice->client->phone)<div class="line">{{ $invoice->client->phone }}</div>@endif
        </td>
        <td class="party-right">
            <div class="label">Terms</div>
            <div class="line">{{ $invoice->payment_terms }}</div>
        </td>
    </x-slot:parties>

    <h3 class="section-heading">Line Items</h3>
    <table class="doc-table num">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num-col">Qty</th>
                <th class="num-col">Unit Price</th>
                <th class="num-col">Discount</th>
                <th class="num-col">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->line_items as $item)
                @php
                    $qty = (float) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];
                    $discountPct = (float) ($item['discount_percent'] ?? 0);
                    $lineTotal = round($qty * $unitPrice * (1 - $discountPct / 100), 2);
                @endphp
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="num-col">{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td>
                    <td class="num-col">R{{ number_format($unitPrice, 2) }}</td>
                    <td class="num-col">{{ $discountPct > 0 ? $discountPct . '%' : '—' }}</td>
                    <td class="num-col">R{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <x-slot:resolution>
        <div class="totals-wrap">
            <table class="totals num">
                <tr><td>Subtotal</td><td class="num-col">R{{ number_format($invoice->subtotal, 2) }}</td></tr>
                <tr><td>VAT ({{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}%)</td><td class="num-col">R{{ number_format($invoice->tax_amount, 2) }}</td></tr>
                @if($invoice->amount_paid > 0)
                    <tr><td>Amount Paid</td><td class="num-col">R{{ number_format($invoice->amount_paid, 2) }}</td></tr>
                @endif
                <tr class="t-rule t-final">
                    <td class="k">{{ $invoice->status === 'paid' ? 'Total' : 'Balance Due' }}</td>
                    <td class="num-col v">R{{ number_format($invoice->status === 'paid' ? $invoice->total : $invoice->balanceDue(), 2) }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->status === 'paid')
            <p style="margin-top:10px; font-size:9.5px; color:#2F5D3A; font-weight:600;">
                PAID IN FULL — {{ $invoice->paid_at->format('d F Y') }}
                @if($invoice->payment_method) via {{ strtoupper($invoice->payment_method) }}@endif
                @if($invoice->payment_reference) · Ref: {{ $invoice->payment_reference }}@endif
            </p>
        @else
            @php
                $bankName    = \App\Models\BillingSetting::get('bank_name');
                $bankAccName = \App\Models\BillingSetting::get('bank_account_name');
                $bankAccNum  = \App\Models\BillingSetting::get('bank_account_number');
                $bankBranch  = \App\Models\BillingSetting::get('bank_branch_code');
            @endphp
            @if($bankName || $bankAccNum)
                <h3 class="section-heading" style="margin-top:16px;">Payment Instructions</h3>
                <table class="doc-table" style="width:100%;">
                    <tbody>
                        <tr>
                            @if($bankName)<td style="width:25%; color:#9CA3AF; font-size:8px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">Bank</td><td>{{ $bankName }}</td>@endif
                            @if($bankAccName)<td style="width:25%; color:#9CA3AF; font-size:8px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">Account Name</td><td>{{ $bankAccName }}</td>@endif
                        </tr>
                        <tr>
                            @if($bankAccNum)<td style="color:#9CA3AF; font-size:8px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">Account Number</td><td>{{ $bankAccNum }}</td>@endif
                            @if($bankBranch)<td style="color:#9CA3AF; font-size:8px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">Branch Code</td><td>{{ $bankBranch }}</td>@endif
                        </tr>
                    </tbody>
                </table>
                <p style="margin-top:6px; font-size:9.5px; color:#1A1A1A;">Use <strong style="margin-left:2px;">{{ $invoice->invoice_number }}</strong> as your payment reference.</p>
            @endif
        @endif

        @if($invoice->notes)
            <p style="margin-top:12px; font-size:9.5px; color:#4B5563;">{{ $invoice->notes }}</p>
        @endif
    </x-slot:resolution>
</x-document-layout>
