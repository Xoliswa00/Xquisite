<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt — {{ $rentPayment->periodLabel() }}</title>
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
        .page { padding: 24px 32px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; }
        .brand-name { font-size: 15px; font-weight: 700; color: #0B2D5B; }
        .doc-block { text-align: right; }
        .doc-title { font-size: 12px; font-weight: 700; letter-spacing: 2px; color: #0B2D5B; text-transform: uppercase; margin-bottom: 5px; }

        .info-matrix { border-collapse: collapse; margin-left: auto; }
        .info-matrix td { padding: 1px 0; font-size: 9.5px; }
        .info-matrix .k { color: #6b7280; padding-right: 14px; white-space: nowrap; text-align: left; }
        .info-matrix .v { color: #1f2937; font-weight: 700; text-align: right; white-space: nowrap; }

        .rule-gold { border: none; border-top: 1.5px solid #C89B3C; margin: 14px 0; }

        .parties { display: flex; justify-content: space-between; gap: 32px; margin-bottom: 14px; }
        .party { flex: 1; }
        .party .label { font-size: 8.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .party .company { font-size: 11px; font-weight: 700; color: #0B2D5B; margin-bottom: 2px; }
        .party .line { font-size: 9.5px; color: #374151; line-height: 1.42; }
        .party.right { text-align: right; }

        .summary { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .summary td { padding: 5px 0; font-size: 10.5px; border-bottom: 0.75px solid #e5e7eb; }
        .summary .k { color: #6b7280; text-align: left; }
        .summary .v { color: #1f2937; font-weight: 600; text-align: right; }
        .total-row td { padding: 10px 0 6px; border-bottom: none; }
        .total-row .k { font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #0B2D5B; text-align: left; }
        .total-row .v { font-size: 20px; font-weight: 700; color: #C89B3C; text-align: right; }
        .total-rule td { border-top: 1.5px solid #C89B3C; padding: 0; line-height: 0; font-size: 0; }

        .paid-stamp { display: inline-block; margin-top: 16px; padding: 4px 12px; border: 1.5px solid #15803d; color: #15803d; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; font-size: 10px; }
        .partial-stamp { display: inline-block; margin-top: 16px; padding: 4px 12px; border: 1.5px solid #b45309; color: #b45309; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; font-size: 10px; }

        .footer-meta { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 28px; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="brand-name serif">{{ $rentPayment->unit?->property?->name }}</div>
        <div class="doc-block">
            <div class="doc-title serif">Receipt</div>
            <table class="info-matrix num">
                <tr><td class="k">Receipt No.</td><td class="v">RP-{{ str_pad($rentPayment->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                <tr><td class="k">Date Paid</td><td class="v">{{ $rentPayment->paid_date?->format('d M Y') ?? '—' }}</td></tr>
                <tr><td class="k">Period</td><td class="v">{{ $rentPayment->periodLabel() }}</td></tr>
            </table>
        </div>
    </div>

    <hr class="rule-gold">

    <div class="parties">
        <div class="party">
            <div class="label">Property</div>
            <div class="company">{{ $rentPayment->unit?->property?->name }}</div>
            <div class="line">{{ $rentPayment->unit?->property?->address_line_1 }}, {{ $rentPayment->unit?->property?->city }}</div>
            <div class="line">Unit {{ $rentPayment->unit?->unit_number }}</div>
        </div>
        <div class="party right">
            <div class="label">Received From</div>
            <div class="company">{{ $rentPayment->renter?->name }}</div>
            @if($rentPayment->renter?->email)<div class="line">{{ $rentPayment->renter->email }}</div>@endif
            @if($rentPayment->renter?->phone)<div class="line">{{ $rentPayment->renter->phone }}</div>@endif
        </div>
    </div>

    <table class="summary num">
        <tr><td class="k">Rent — {{ $rentPayment->periodLabel() }}</td><td class="v">R{{ number_format($rentPayment->amount_due, 2) }}</td></tr>
        <tr><td class="k">Payment Method</td><td class="v">{{ $rentPayment->payment_method ? strtoupper(str_replace('_', ' ', $rentPayment->payment_method)) : '—' }}</td></tr>
        @if($rentPayment->reference)
            <tr><td class="k">Reference</td><td class="v">{{ $rentPayment->reference }}</td></tr>
        @endif
        <tr class="total-rule"><td colspan="2"></td></tr>
        <tr class="total-row">
            <td class="k">Amount Received</td>
            <td class="v">R{{ number_format($rentPayment->amount_paid, 2) }}</td>
        </tr>
        @if($rentPayment->status === 'partial')
            <tr><td class="k">Outstanding Balance</td><td class="v">R{{ number_format($rentPayment->amount_due - $rentPayment->amount_paid, 2) }}</td></tr>
        @endif
    </table>

    @if($rentPayment->status === 'paid')
        <div class="paid-stamp">Paid in Full</div>
    @elseif($rentPayment->status === 'partial')
        <div class="partial-stamp">Partial Payment</div>
    @endif

    <div class="footer-meta">Generated {{ now()->format('d M Y H:i') }} &middot; Receipt for Rent Payment #{{ $rentPayment->id }}</div>

</div>
</body>
</html>
