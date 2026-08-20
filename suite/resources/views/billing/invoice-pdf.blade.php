@php
    $badge = $invoice->status_badge;
    $statusVariant = match(true) {
        $invoice->status === 'paid'   => 'positive',
        $invoice->status === 'overdue' => 'negative',
        default => null,
    };
    $dueDays = (int) (\App\Models\BillingSetting::get('invoice_due_days') ?? 7);
    $daysUntilDue = $invoice->days_until_due;
    $dueUrgencyText = match(true) {
        $invoice->status === 'paid' => null,
        $daysUntilDue < 0  => 'Overdue by ' . abs($daysUntilDue) . ' day' . (abs($daysUntilDue) === 1 ? '' : 's'),
        $daysUntilDue === 0 => 'Due today',
        default            => 'Due in ' . $daysUntilDue . ' day' . ($daysUntilDue === 1 ? '' : 's'),
    };
    $modules = $invoice->tenant->activeModules()->with('platformModule')->get();
    $paymentsReceived = $invoice->status === 'paid' ? (float) $invoice->amount : 0.0;
    $balanceDue = (float) $invoice->amount - $paymentsReceived;

    $bankName    = \App\Models\BillingSetting::get('bank_name');
    $bankAccName = \App\Models\BillingSetting::get('bank_account_name');
    $bankAccNum  = \App\Models\BillingSetting::get('bank_account_number');
    $bankBranch  = \App\Models\BillingSetting::get('bank_branch_code');
@endphp
<x-document-layout
    doc-type="Tax Invoice"
    :reference="$invoice->invoice_number"
    date-label="Issue Date"
    :date="$invoice->created_at->format('d M Y')"
    second-date-label="Due Date"
    :second-date="$invoice->due_date->format('d M Y')"
    :status-label="strtoupper($badge['label'])"
    :status-variant="$statusVariant"
>
    <x-slot:parties>
        <td>
            <div class="label">Bill To</div>
            <div class="name">{{ $invoice->tenant->name }}</div>
            @if($invoice->tenant->address)<div class="line">{{ $invoice->tenant->address }}</div>@endif
            @if($invoice->tenant->vat_number)<div class="line">VAT {{ $invoice->tenant->vat_number }}</div>@endif
            @if($invoice->tenant->phone)<div class="line">{{ $invoice->tenant->phone }}</div>@endif
            @if($invoice->tenant->email)<div class="line">{{ $invoice->tenant->email }}</div>@endif
        </td>
        <td class="party-right">
            <div class="label">Terms</div>
            <div class="line">Net {{ $dueDays }}</div>
            <div class="label" style="margin-top:6px;">Currency</div>
            <div class="line">ZAR</div>
        </td>
    </x-slot:parties>

    <h3 class="section-heading">Line Items</h3>
    <table class="doc-table num">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num-col">Qty</th>
                <th class="num-col">Unit Price</th>
                <th class="num-col">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($modules->count())
                @foreach($modules as $tm)
                    <tr>
                        <td>{{ $tm->platformModule?->name ?? ucfirst(str_replace('_', ' ', $tm->module)) }} — Platform Module</td>
                        <td class="num-col">1</td>
                        <td class="num-col">{{ number_format($tm->monthly_price, 2) }}</td>
                        <td class="num-col">{{ number_format($tm->monthly_price, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>Platform Subscription</td>
                    <td class="num-col">1</td>
                    <td class="num-col">{{ number_format($invoice->amount, 2) }}</td>
                    <td class="num-col">{{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <x-slot:resolution>
        <div class="totals-wrap">
            <table class="totals num">
                <tr><td>Subtotal</td><td class="num-col">R{{ number_format($invoice->amount, 2) }}</td></tr>
                <tr><td>VAT</td><td class="num-col">R0.00</td></tr>
                <tr><td>Payments Received</td><td class="num-col">R{{ number_format($paymentsReceived, 2) }}</td></tr>
                <tr class="t-rule t-final">
                    <td class="k">{{ $invoice->status === 'paid' ? 'Balance' : 'Total Due' }}</td>
                    <td class="num-col v">R{{ number_format($balanceDue, 2) }}</td>
                </tr>
                @if($dueUrgencyText)
                    <tr><td colspan="2" class="num-col" style="color: {{ $daysUntilDue < 0 ? '#8B2C2C' : '#9CA3AF' }};">{{ $dueUrgencyText }}</td></tr>
                @endif
            </table>
        </div>

        @if($invoice->status === 'paid')
            <p style="margin-top:10px; font-size:9.5px; color:#2F5D3A; font-weight:600;">
                PAID IN FULL — {{ $invoice->paid_at->format('d F Y') }}
                @if($invoice->payment_method) via {{ $invoice->payment_method }}@endif
                @if($invoice->payment_reference) · Ref: {{ $invoice->payment_reference }}@endif
            </p>
        @elseif(in_array($invoice->status, ['unpaid', 'overdue']) && ($bankName || $bankAccNum))
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
            <p style="margin-top:6px; font-size:9.5px; color:#1A1A1A;">Use <strong>{{ $invoice->invoice_number }}</strong> as your payment reference — payments without a reference may be delayed.</p>
        @endif
    </x-slot:resolution>
</x-document-layout>
