<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }

        .page { padding: 40px 48px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 36px; padding-bottom: 24px; border-bottom: 2px solid #D4AF37; }
        .brand-name { font-size: 22px; font-weight: 700; color: #002B5B; letter-spacing: -0.5px; }
        .brand-tagline { font-size: 10px; color: #64748b; margin-top: 2px; }
        .invoice-label { text-align: right; }
        .invoice-label .title { font-size: 28px; font-weight: 700; color: #002B5B; letter-spacing: 2px; text-transform: uppercase; }
        .invoice-label .number { font-size: 13px; color: #0078D4; font-weight: 600; margin-top: 4px; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; }
        .status-paid     { background: #d1fae5; color: #065f46; }
        .status-unpaid   { background: #fef3c7; color: #92400e; }
        .status-overdue  { background: #fee2e2; color: #991b1b; }
        .status-pop      { background: #dbeafe; color: #1e40af; }

        /* Address block */
        .addresses { display: flex; justify-content: space-between; margin-bottom: 32px; gap: 24px; }
        .address-block { flex: 1; }
        .address-block .label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 8px; }
        .address-block .company { font-size: 13px; font-weight: 700; color: #002B5B; margin-bottom: 4px; }
        .address-block .detail { font-size: 11px; color: #475569; line-height: 1.6; }

        /* Invoice meta */
        .meta-table { width: 100%; margin-bottom: 28px; border-collapse: collapse; }
        .meta-table td { padding: 6px 12px; font-size: 11px; }
        .meta-table tr:nth-child(odd) td { background: #f8fafc; }
        .meta-table .meta-label { color: #64748b; font-weight: 600; width: 40%; }
        .meta-table .meta-value { color: #1e293b; font-weight: 500; }

        /* Line items */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .items-table thead tr { background: #002B5B; }
        .items-table thead th { padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; }
        .items-table thead th.text-right { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .items-table tbody tr:last-child { border-bottom: none; }
        .items-table tbody td { padding: 10px 12px; font-size: 11px; color: #334155; }
        .items-table tbody td.text-right { text-align: right; }
        .items-table .period { font-size: 10px; color: #94a3b8; margin-top: 2px; }

        /* Total row */
        .total-section { margin-top: 0; }
        .total-row { display: flex; justify-content: flex-end; padding: 16px 12px; background: #f8fafc; border-top: 2px solid #D4AF37; }
        .total-label { font-size: 14px; font-weight: 700; color: #002B5B; margin-right: 40px; }
        .total-amount { font-size: 20px; font-weight: 700; color: #D4AF37; }

        /* Payment section */
        .section { margin-top: 28px; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #D4AF37; margin-bottom: 10px; padding-bottom: 4px; border-bottom: 1px solid #D4AF37; }

        .banking-grid { display: flex; flex-wrap: wrap; gap: 0; }
        .banking-item { width: 50%; padding: 6px 0; }
        .banking-item .b-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: 600; }
        .banking-item .b-value { font-size: 11px; color: #1e293b; font-weight: 500; margin-top: 1px; }
        .reference-note { margin-top: 10px; padding: 10px 14px; background: #fef3c7; border-left: 3px solid #D4AF37; border-radius: 0 4px 4px 0; font-size: 11px; color: #92400e; }

        /* Paid stamp */
        .paid-info { margin-top: 10px; padding: 10px 14px; background: #d1fae5; border-left: 3px solid #10b981; border-radius: 0 4px 4px 0; font-size: 11px; color: #065f46; }

        /* Footer */
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <img src="{{ public_path('img/android-icon-192x192.png') }}" alt="Logo" style="height:52px;width:auto;margin-bottom:8px;display:block;">
            <div class="brand-name">{{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }}</div>
            @php $companyVat = \App\Models\BillingSetting::get('company_vat'); @endphp
            @if($companyVat)
                <div class="brand-tagline">VAT Reg: {{ $companyVat }}</div>
            @endif
            @php $companyEmail = \App\Models\BillingSetting::get('company_email'); @endphp
            @if($companyEmail)
                <div class="brand-tagline">{{ $companyEmail }}</div>
            @endif
            @php $companyPhone = \App\Models\BillingSetting::get('company_phone'); @endphp
            @if($companyPhone)
                <div class="brand-tagline">{{ $companyPhone }}</div>
            @endif
        </div>
        <div class="invoice-label">
            <div class="title">Invoice</div>
            <div class="number">{{ $invoice->invoice_number }}</div>
            @php $badge = $invoice->status_badge; @endphp
            @php
                $statusClass = match(true) {
                    $invoice->status === 'paid'                   => 'status-paid',
                    $invoice->isAwaitingConfirmation()            => 'status-pop',
                    $invoice->status === 'overdue'                => 'status-overdue',
                    default                                       => 'status-unpaid',
                };
            @endphp
            <div><span class="status-badge {{ $statusClass }}">{{ $badge['label'] }}</span></div>
        </div>
    </div>

    {{-- From / To --}}
    <div class="addresses">
        <div class="address-block">
            <div class="label">From</div>
            <div class="company">{{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }}</div>
            @php $addr = \App\Models\BillingSetting::get('company_address'); @endphp
            @if($addr)
                <div class="detail">{{ $addr }}</div>
            @endif
            @if($companyEmail)<div class="detail">{{ $companyEmail }}</div>@endif
            @if($companyPhone)<div class="detail">{{ $companyPhone }}</div>@endif
            @if($companyVat)<div class="detail">VAT: {{ $companyVat }}</div>@endif
        </div>
        <div class="address-block" style="text-align:right;">
            <div class="label">Billed To</div>
            <div class="company">{{ $invoice->tenant->name }}</div>
            @if($invoice->tenant->address)
                <div class="detail">{{ $invoice->tenant->address }}</div>
            @endif
            @if($invoice->tenant->email)
                <div class="detail">{{ $invoice->tenant->email }}</div>
            @endif
            @if($invoice->tenant->phone)
                <div class="detail">{{ $invoice->tenant->phone }}</div>
            @endif
            @if($invoice->tenant->vat_number)
                <div class="detail">VAT: {{ $invoice->tenant->vat_number }}</div>
            @endif
        </div>
    </div>

    {{-- Invoice meta --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">Invoice Date</td>
            <td class="meta-value">{{ $invoice->created_at->format('d F Y') }}</td>
            <td class="meta-label">Due Date</td>
            <td class="meta-value" style="color: {{ $invoice->status === 'overdue' ? '#dc2626' : '#1e293b' }}; font-weight: 700;">{{ $invoice->due_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Billing Period</td>
            <td class="meta-value">{{ $invoice->billing_period_start->format('d M Y') }} – {{ $invoice->billing_period_end->format('d M Y') }}</td>
            <td class="meta-label">Payment Reference</td>
            <td class="meta-value" style="color:#0078D4; font-weight:700;">{{ $invoice->invoice_number }}</td>
        </tr>
    </table>

    {{-- Line items --}}
    @php
        $modules = $invoice->tenant->activeModules()->with('platformModule')->get();
    @endphp
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($modules->count())
                @foreach($modules as $tm)
                    <tr>
                        <td>
                            {{ $tm->platformModule?->name ?? ucfirst(str_replace('_', ' ', $tm->module)) }} — Platform Module
                            <div class="period">Monthly subscription · {{ $invoice->billing_period_start->format('d M') }} – {{ $invoice->billing_period_end->format('d M Y') }}</div>
                        </td>
                        <td class="text-right">R{{ number_format($tm->monthly_price, 2) }}</td>
                        <td class="text-right">R{{ number_format($tm->monthly_price, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>
                        Platform Subscription
                        <div class="period">{{ $invoice->billing_period_start->format('d M') }} – {{ $invoice->billing_period_end->format('d M Y') }}</div>
                    </td>
                    <td class="text-right">R{{ number_format($invoice->amount, 2) }}</td>
                    <td class="text-right">R{{ number_format($invoice->amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Total Due</span>
            <span class="total-amount">R{{ number_format($invoice->amount, 2) }}</span>
        </div>
    </div>

    {{-- Paid confirmation --}}
    @if($invoice->status === 'paid')
        <div class="paid-info">
            Paid on {{ $invoice->paid_at->format('d F Y') }}
            @if($invoice->payment_method) via {{ $invoice->payment_method }}@endif
            @if($invoice->payment_reference) · Ref: {{ $invoice->payment_reference }}@endif
        </div>
    @endif

    {{-- Banking details (only for unpaid/overdue) --}}
    @if(in_array($invoice->status, ['unpaid', 'overdue']))
        @php
            $bankName    = \App\Models\BillingSetting::get('bank_name');
            $bankAccName = \App\Models\BillingSetting::get('bank_account_name');
            $bankAccNum  = \App\Models\BillingSetting::get('bank_account_number');
            $bankBranch  = \App\Models\BillingSetting::get('bank_branch_code');
        @endphp
        @if($bankName || $bankAccNum)
            <div class="section">
                <div class="section-title">EFT Payment Details</div>
                <div class="banking-grid">
                    @if($bankName)
                        <div class="banking-item">
                            <div class="b-label">Bank</div>
                            <div class="b-value">{{ $bankName }}</div>
                        </div>
                    @endif
                    @if($bankAccName)
                        <div class="banking-item">
                            <div class="b-label">Account Name</div>
                            <div class="b-value">{{ $bankAccName }}</div>
                        </div>
                    @endif
                    @if($bankAccNum)
                        <div class="banking-item">
                            <div class="b-label">Account Number</div>
                            <div class="b-value">{{ $bankAccNum }}</div>
                        </div>
                    @endif
                    @if($bankBranch)
                        <div class="banking-item">
                            <div class="b-label">Branch Code</div>
                            <div class="b-value">{{ $bankBranch }}</div>
                        </div>
                    @endif
                </div>
                <div class="reference-note">
                    <strong>Important:</strong> Use <strong>{{ $invoice->invoice_number }}</strong> as your payment reference. Payments without a reference may be delayed.
                </div>
            </div>
        @endif
    @endif

    <div class="footer">
        Generated by {{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }} · {{ now()->format('d M Y') }} · {{ $invoice->invoice_number }}
    </div>

</div>
</body>
</html>
