<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.35;
            color: #1f2937;
            background: #fff;
        }
        .serif { font-family: "DejaVu Serif", Georgia, serif; }
        .num { font-variant-numeric: tabular-nums; }

        .page { padding: 18px 28px; }

        /* ── Header ─────────────────────────────────────────── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; }

        .brand-block { display: flex; align-items: center; gap: 10px; }
        .brand-block img { height: 34px; width: auto; display: block; }
        .brand-name { font-size: 15px; font-weight: 700; color: #0B2D5B; }
        .brand-sub { font-size: 8.5px; color: #6b7280; margin-top: 1px; }

        .doc-block { text-align: right; }
        .doc-title { font-size: 12px; font-weight: 700; letter-spacing: 2px; color: #0B2D5B; text-transform: uppercase; margin-bottom: 5px; }

        .info-matrix { border-collapse: collapse; margin-left: auto; }
        .info-matrix td { padding: 1px 0; font-size: 9.5px; }
        .info-matrix .k { color: #6b7280; padding-right: 14px; white-space: nowrap; text-align: left; }
        .info-matrix .v { color: #1f2937; font-weight: 700; text-align: right; white-space: nowrap; }
        .info-matrix .v.status-unpaid   { color: #0B2D5B; }
        .info-matrix .v.status-overdue  { color: #991b1b; }
        .info-matrix .v.status-paid     { color: #166534; }
        .info-matrix .v.status-pop      { color: #0B2D5B; }

        .rule-gold  { border: none; border-top: 1.5px solid #C89B3C; margin: 10px 0; }
        .rule-gray  { border: none; border-top: 0.75px solid #d1d5db; }

        /* ── From / Bill To ─────────────────────────────────── */
        .parties { display: flex; justify-content: space-between; gap: 32px; margin-bottom: 10px; }
        .party { flex: 1; }
        .party .label { font-size: 8.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .party .company { font-size: 11px; font-weight: 700; color: #0B2D5B; margin-bottom: 2px; }
        .party .line { font-size: 9.5px; color: #374151; line-height: 1.42; }
        .party.right { text-align: right; }

        /* ── Items table ────────────────────────────────────── */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead th {
            padding: 4px 0 5px; text-align: left; font-size: 8.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.75px; color: #0B2D5B;
            border-bottom: 1.5px solid #0B2D5B;
        }
        .items-table thead th.num-col { text-align: right; }
        .items-table tbody td { padding: 5px 0; font-size: 10px; color: #1f2937; border-bottom: 0.75px solid #e5e7eb; }
        .items-table tbody td.num-col { text-align: right; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .col-qty, .col-price, .col-disc, .col-vat, .col-amt { width: 11%; }
        .col-desc { width: 45%; }

        /* ── Summary (table-based: dompdf's flex justify-content is unreliable for narrow inline k:v pairs) ── */
        .summary-wrap { display: flex; justify-content: flex-end; margin-top: 2px; }
        .summary { width: 260px; border-collapse: collapse; }
        .summary td { padding: 2.5px 0; font-size: 10px; }
        .summary .k { color: #6b7280; font-weight: 700; text-align: left; }
        .summary .v { color: #1f2937; font-weight: 600; text-align: right; }

        .total-row td { padding: 6px 0 5px; }
        .total-row .k { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #0B2D5B; text-align: left; }
        .total-row .v { font-size: 18px; font-weight: 700; color: #C89B3C; text-align: right; }
        .total-rule td { border-top: 1.5px solid #C89B3C; padding: 0; line-height: 0; font-size: 0; }
        .urgency-row td { padding: 0 0 2px; text-align: right; font-size: 9.5px; font-weight: 600; }

        /* ── Paid confirmation ──────────────────────────────── */
        .paid-line { margin-top: 8px; padding-top: 6px; border-top: 0.75px solid #d1d5db; font-size: 10px; color: #166534; font-weight: 600; }

        /* ── Payment instructions ───────────────────────────── */
        .section-heading { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #0B2D5B; padding-bottom: 3px; border-bottom: 1px solid #C89B3C; margin-bottom: 6px; }
        .pay-grid { display: flex; flex-wrap: wrap; }
        .pay-item { width: 50%; padding: 2px 0; }
        .pay-item .k { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; }
        .pay-item .v { font-size: 10px; color: #1f2937; font-weight: 700; margin-top: 1px; }
        .pay-ref { margin-top: 5px; font-size: 10px; color: #0B2D5B; }
        .pay-ref strong { font-weight: 700; }

        /* ── Footer ─────────────────────────────────────────── */
        .footer-grid { display: flex; justify-content: space-between; gap: 24px; }
        .footer-col { flex: 1; }
        .footer-col .k { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.75px; color: #6b7280; margin-bottom: 2px; }
        .footer-col .v { font-size: 8.5px; color: #374151; line-height: 1.3; }
        .footer-thanks { text-align: center; font-size: 9.5px; color: #374151; margin: 8px 0 4px; }
        .footer-meta { text-align: center; font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="brand-block">
            <img src="{{ public_path('img/android-icon-192x192.png') }}" alt="Logo">
            <div>
                <div class="brand-name serif">{{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }}</div>
                @if($companyVat = \App\Models\BillingSetting::get('company_vat'))
                    <div class="brand-sub">VAT {{ $companyVat }}</div>
                @endif
            </div>
        </div>
        <div class="doc-block">
            <div class="doc-title serif">Tax Invoice</div>
            @php
                $badge = $invoice->status_badge;
                $statusClass = match(true) {
                    $invoice->status === 'paid'        => 'status-paid',
                    $invoice->isAwaitingConfirmation()  => 'status-pop',
                    $invoice->status === 'overdue'      => 'status-overdue',
                    default                              => 'status-unpaid',
                };
                $dueDays = (int) (\App\Models\BillingSetting::get('invoice_due_days') ?? 7);
            @endphp
            <table class="info-matrix num">
                <tr><td class="k">Invoice No</td><td class="v">{{ $invoice->invoice_number }}</td></tr>
                <tr><td class="k">Issue Date</td><td class="v">{{ $invoice->created_at->format('d M Y') }}</td></tr>
                <tr><td class="k">Due Date</td><td class="v">{{ $invoice->due_date->format('d M Y') }}</td></tr>
                <tr><td class="k">Terms</td><td class="v">Net {{ $dueDays }}</td></tr>
                <tr><td class="k">Reference</td><td class="v">{{ $invoice->invoice_number }}</td></tr>
                <tr><td class="k">Currency</td><td class="v">ZAR</td></tr>
                <tr><td class="k">Status</td><td class="v {{ $statusClass }}">{{ strtoupper($badge['label']) }}</td></tr>
            </table>
        </div>
    </div>

    <hr class="rule-gold">

    {{-- From / Bill To --}}
    <div class="parties">
        <div class="party">
            <div class="label">From</div>
            <div class="company">{{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }}</div>
            @if($addr = \App\Models\BillingSetting::get('company_address'))
                <div class="line">{{ $addr }}</div>
            @endif
            @if($companyVat)<div class="line">VAT {{ $companyVat }}</div>@endif
            @if($companyPhone = \App\Models\BillingSetting::get('company_phone'))<div class="line">{{ $companyPhone }}</div>@endif
            @if($companyEmail = \App\Models\BillingSetting::get('company_email'))<div class="line">{{ $companyEmail }}</div>@endif
        </div>
        <div class="party right">
            <div class="label">Bill To</div>
            <div class="company">{{ $invoice->tenant->name }}</div>
            @if($invoice->tenant->address)<div class="line">{{ $invoice->tenant->address }}</div>@endif
            @if($invoice->tenant->vat_number)<div class="line">VAT {{ $invoice->tenant->vat_number }}</div>@endif
            @if($invoice->tenant->phone)<div class="line">{{ $invoice->tenant->phone }}</div>@endif
            @if($invoice->tenant->email)<div class="line">{{ $invoice->tenant->email }}</div>@endif
        </div>
    </div>

    <hr class="rule-gold">

    {{-- Line items --}}
    @php
        $modules = $invoice->tenant->activeModules()->with('platformModule')->get();
    @endphp
    <table class="items-table num">
        <thead>
            <tr>
                <th class="col-desc">Description</th>
                <th class="num-col col-qty">Qty</th>
                <th class="num-col col-price">Unit Price</th>
                <th class="num-col col-disc">Discount</th>
                <th class="num-col col-vat">VAT</th>
                <th class="num-col col-amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($modules->count())
                @foreach($modules as $tm)
                    <tr>
                        <td class="col-desc">{{ $tm->platformModule?->name ?? ucfirst(str_replace('_', ' ', $tm->module)) }} — Platform Module</td>
                        <td class="num-col col-qty">1</td>
                        <td class="num-col col-price">{{ number_format($tm->monthly_price, 2) }}</td>
                        <td class="num-col col-disc">0.00</td>
                        <td class="num-col col-vat">0.00</td>
                        <td class="num-col col-amt">{{ number_format($tm->monthly_price, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="col-desc">Platform Subscription</td>
                    <td class="num-col col-qty">1</td>
                    <td class="num-col col-price">{{ number_format($invoice->amount, 2) }}</td>
                    <td class="num-col col-disc">0.00</td>
                    <td class="num-col col-vat">0.00</td>
                    <td class="num-col col-amt">{{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Summary --}}
    @php
        $paymentsReceived = $invoice->status === 'paid' ? (float) $invoice->amount : 0.0;
        $balanceDue = (float) $invoice->amount - $paymentsReceived;
        $daysUntilDue = $invoice->days_until_due;
        $dueUrgencyText = match(true) {
            $invoice->status === 'paid' => null,
            $daysUntilDue < 0  => 'Overdue by ' . abs($daysUntilDue) . ' day' . (abs($daysUntilDue) === 1 ? '' : 's'),
            $daysUntilDue === 0 => 'Due today',
            default            => 'Due in ' . $daysUntilDue . ' day' . ($daysUntilDue === 1 ? '' : 's'),
        };
    @endphp
    <div class="summary-wrap">
        <table class="summary num">
            <tr><td class="k">Subtotal</td><td class="v">R{{ number_format($invoice->amount, 2) }}</td></tr>
            <tr><td class="k">VAT</td><td class="v">R0.00</td></tr>
            <tr><td class="k">Discount</td><td class="v">R0.00</td></tr>
            <tr><td class="k">Payments Received</td><td class="v">R{{ number_format($paymentsReceived, 2) }}</td></tr>
            <tr class="total-rule"><td colspan="2"></td></tr>
            <tr class="total-row">
                <td class="k">{{ $invoice->status === 'paid' ? 'Balance' : 'Total Due' }}</td>
                <td class="v">R{{ number_format($balanceDue, 2) }}</td>
            </tr>
            @if($dueUrgencyText)
                <tr class="urgency-row"><td colspan="2" style="color: {{ $daysUntilDue < 0 ? '#991b1b' : '#6b7280' }};">{{ $dueUrgencyText }}</td></tr>
            @endif
        </table>
    </div>

    {{-- Paid confirmation --}}
    @if($invoice->status === 'paid')
        <div class="paid-line">
            PAID IN FULL — {{ $invoice->paid_at->format('d F Y') }}
            @if($invoice->payment_method) via {{ $invoice->payment_method }}@endif
            @if($invoice->payment_reference) · Ref: {{ $invoice->payment_reference }}@endif
        </div>
    @endif

    {{-- Payment instructions (only for unpaid/overdue) --}}
    @if(in_array($invoice->status, ['unpaid', 'overdue']))
        @php
            $bankName    = \App\Models\BillingSetting::get('bank_name');
            $bankAccName = \App\Models\BillingSetting::get('bank_account_name');
            $bankAccNum  = \App\Models\BillingSetting::get('bank_account_number');
            $bankBranch  = \App\Models\BillingSetting::get('bank_branch_code');
        @endphp
        @if($bankName || $bankAccNum)
            <div style="margin-top: 10px;">
                <div class="section-heading">Payment Instructions</div>
                <div class="pay-grid num">
                    @if($bankName)<div class="pay-item"><div class="k">Bank</div><div class="v">{{ $bankName }}</div></div>@endif
                    @if($bankAccName)<div class="pay-item"><div class="k">Account Name</div><div class="v">{{ $bankAccName }}</div></div>@endif
                    @if($bankAccNum)<div class="pay-item"><div class="k">Account Number</div><div class="v">{{ $bankAccNum }}</div></div>@endif
                    @if($bankBranch)<div class="pay-item"><div class="k">Branch Code</div><div class="v">{{ $bankBranch }}</div></div>@endif
                </div>
                <div class="pay-ref">Use <strong>{{ $invoice->invoice_number }}</strong> as your payment reference — payments without a reference may be delayed.</div>
            </div>
        @endif
    @endif

    {{-- Footer --}}
    <hr class="rule-gray" style="margin: 8px 0 6px;">
    <div class="footer-grid">
        <div class="footer-col">
            <div class="k">Payment Terms</div>
            <div class="v">Net {{ $dueDays }} — payment due within {{ $dueDays }} day{{ $dueDays === 1 ? '' : 's' }} of the invoice date.</div>
        </div>
        <div class="footer-col">
            <div class="k">Support</div>
            <div class="v">
                @if($companyEmail){{ $companyEmail }}<br>@endif
                @if($companyPhone){{ $companyPhone }}@endif
            </div>
        </div>
        <div class="footer-col" style="text-align:right;">
            <div class="k">Company</div>
            <div class="v">
                @if($companyReg = \App\Models\BillingSetting::get('company_registration'))Reg {{ $companyReg }}<br>@endif
                @if($companyVat)VAT {{ $companyVat }}<br>@endif
                @if($companyWeb = \App\Models\BillingSetting::get('company_website')){{ $companyWeb }}@endif
            </div>
        </div>
    </div>

    <div class="footer-thanks">Thank you for your business.</div>
    <div class="footer-meta">Generated by {{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }} · {{ now()->format('d M Y') }} · {{ $invoice->invoice_number }}</div>

</div>
</body>
</html>
