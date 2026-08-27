<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement — Lease #{{ $lease->id }}</title>
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

        .header { display: flex; justify-content: space-between; align-items: flex-start; }
        .brand-name { font-size: 15px; font-weight: 700; color: #0B2D5B; }
        .doc-block { text-align: right; }
        .doc-title { font-size: 12px; font-weight: 700; letter-spacing: 2px; color: #0B2D5B; text-transform: uppercase; margin-bottom: 5px; }

        .info-matrix { border-collapse: collapse; margin-left: auto; }
        .info-matrix td { padding: 1px 0; font-size: 9.5px; }
        .info-matrix .k { color: #6b7280; padding-right: 14px; white-space: nowrap; text-align: left; }
        .info-matrix .v { color: #1f2937; font-weight: 700; text-align: right; white-space: nowrap; }

        .rule-gold { border: none; border-top: 1.5px solid #C89B3C; margin: 10px 0; }
        .rule-gray { border: none; border-top: 0.75px solid #d1d5db; }

        .parties { display: flex; justify-content: space-between; gap: 32px; margin-bottom: 10px; }
        .party { flex: 1; }
        .party .label { font-size: 8.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .party .company { font-size: 11px; font-weight: 700; color: #0B2D5B; margin-bottom: 2px; }
        .party .line { font-size: 9.5px; color: #374151; line-height: 1.42; }
        .party.right { text-align: right; }

        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead th {
            padding: 4px 0 5px; text-align: left; font-size: 8.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.75px; color: #0B2D5B;
            border-bottom: 1.5px solid #0B2D5B;
        }
        .items-table thead th.num-col { text-align: right; }
        .items-table tbody td { padding: 4px 0; font-size: 9.5px; color: #1f2937; border-bottom: 0.75px solid #e5e7eb; }
        .items-table tbody td.num-col { text-align: right; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .col-date { width: 11%; }
        .col-desc { width: 33%; }
        .col-excl, .col-vat, .col-incl, .col-paid, .col-bal { width: 11%; }
        .negative { color: #991b1b; }

        .summary-wrap { display: flex; justify-content: flex-end; margin-top: 6px; }
        .summary { width: 260px; border-collapse: collapse; }
        .summary td { padding: 2.5px 0; font-size: 10px; }
        .summary .k { color: #6b7280; font-weight: 700; text-align: left; }
        .summary .v { color: #1f2937; font-weight: 600; text-align: right; }
        .total-row td { padding: 6px 0 5px; }
        .total-row .k { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #0B2D5B; text-align: left; }
        .total-row .v { font-size: 18px; font-weight: 700; color: #C89B3C; text-align: right; }
        .total-rule td { border-top: 1.5px solid #C89B3C; padding: 0; line-height: 0; font-size: 0; }

        .section-heading { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #0B2D5B; padding-bottom: 3px; border-bottom: 1px solid #C89B3C; margin-bottom: 6px; margin-top: 14px; }
        .footer-meta { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 14px; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="brand-name serif">{{ $lease->property?->name }}</div>
        <div class="doc-block">
            <div class="doc-title serif">Statement</div>
            <table class="info-matrix num">
                <tr><td class="k">Lease</td><td class="v">#{{ $lease->id }}</td></tr>
                <tr><td class="k">Generated</td><td class="v">{{ now()->format('d M Y') }}</td></tr>
                <tr><td class="k">Status</td><td class="v">{{ ucfirst($lease->status) }}</td></tr>
            </table>
        </div>
    </div>

    <hr class="rule-gold">

    <div class="parties">
        <div class="party">
            <div class="label">Property</div>
            <div class="company">{{ $lease->property?->name }}</div>
            <div class="line">{{ $lease->property?->address_line_1 }}, {{ $lease->property?->city }}</div>
            <div class="line">Unit {{ $lease->unit?->unit_number }}</div>
        </div>
        <div class="party right">
            <div class="label">Renter</div>
            <div class="company">{{ $lease->renter?->name }}</div>
            @if($lease->renter?->email)<div class="line">{{ $lease->renter->email }}</div>@endif
            @if($lease->renter?->phone)<div class="line">{{ $lease->renter->phone }}</div>@endif
        </div>
    </div>

    <hr class="rule-gold">

    <table class="items-table num">
        <thead>
            <tr>
                <th class="col-date">Date</th>
                <th class="col-desc">Description</th>
                <th class="num-col col-excl">Excl.</th>
                <th class="num-col col-vat">VAT</th>
                <th class="num-col col-incl">Incl.</th>
                <th class="num-col col-paid">Paid</th>
                <th class="num-col col-bal">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $runningBalance = 0; @endphp
            @forelse($entries as $entry)
                @php $runningBalance += $entry['incl'] - $entry['paid']; @endphp
                <tr>
                    <td class="col-date">{{ $entry['date']->format('d M Y') }}</td>
                    <td class="col-desc">{{ $entry['description'] }}</td>
                    <td class="num-col col-excl {{ $entry['excl'] < 0 ? 'negative' : '' }}">{{ number_format($entry['excl'], 2) }}</td>
                    <td class="num-col col-vat">{{ number_format($entry['vat'], 2) }}</td>
                    <td class="num-col col-incl {{ $entry['incl'] < 0 ? 'negative' : '' }}">{{ number_format($entry['incl'], 2) }}</td>
                    <td class="num-col col-paid">{{ number_format($entry['paid'], 2) }}</td>
                    <td class="num-col col-bal {{ $runningBalance < 0 ? 'negative' : '' }}">{{ number_format($runningBalance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center; padding: 10px 0; color:#9ca3af;">No transactions recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-wrap">
        <table class="summary num">
            <tr class="total-rule"><td colspan="2"></td></tr>
            <tr class="total-row">
                <td class="k">Balance Due</td>
                <td class="v">R{{ number_format($runningBalance, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section-heading">Deposit</div>
    <table class="summary num" style="width: 100%;">
        <tr><td class="k">Deposit Held</td><td class="v">R{{ number_format($lease->deposit_amount, 2) }}</td></tr>
        @if($lease->deposit_status === 'refunded')
            <tr><td class="k">Refunded</td><td class="v">R{{ number_format($lease->deposit_refund_amount, 2) }} on {{ \Carbon\Carbon::parse($lease->deposit_refund_date)->format('d M Y') }}</td></tr>
        @else
            <tr><td class="k">Status</td><td class="v">{{ $lease->deposit_paid ? 'Held' : 'Not yet paid' }}</td></tr>
        @endif
    </table>

    <div class="footer-meta">Generated {{ now()->format('d M Y H:i') }} · Lease #{{ $lease->id }}</div>

</div>
</body>
</html>
