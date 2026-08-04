<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->reference }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 24px; }
        h1 { font-size: 18px; color: #B8860B; text-align: center; margin: 0 0 4px; }
        .subtitle { text-align: center; color: #64748b; font-size: 10px; margin: 0 0 20px; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta .label { color: #64748b; width: 40%; }
        .items { margin-top: 16px; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
        .items th { text-align: left; font-size: 10px; color: #64748b; padding: 6px 4px; border-bottom: 1px solid #cbd5e1; }
        .items td { padding: 6px 4px; font-size: 11px; }
        .items th.right, .items td.right { text-align: right; }
        .totals td { padding: 3px 0; }
        .totals .right { text-align: right; }
        .grand { font-weight: bold; font-size: 13px; border-top: 1px solid #cbd5e1; padding-top: 6px !important; }
        .status { text-align: center; margin-top: 16px; font-weight: bold; }
        .status.paid { color: #059669; }
        .status.voided { color: #dc2626; }
        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <h1>Xquisite Creation</h1>
    <p class="subtitle">{{ now()->format('d M Y, H:i') }}</p>

    <table class="meta">
        <tr>
            <td class="label">Reference</td>
            <td>{{ $sale->reference }}</td>
            <td class="label">Date</td>
            <td>{{ $sale->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Customer</td>
            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
            <td class="label">Served by</td>
            <td>{{ $sale->appointment?->staff?->name ?? '—' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->name }} ({{ ucfirst($item->item_type) }})</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">R{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">R{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals" style="margin-top: 10px;">
        <tr>
            <td>Subtotal</td>
            <td class="right">R{{ number_format($sale->subtotal, 2) }}</td>
        </tr>
        @if($sale->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td class="right">−R{{ number_format($sale->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td>TOTAL</td>
            <td class="right">R{{ number_format($sale->total, 2) }}</td>
        </tr>
        <tr>
            <td style="color:#64748b; font-size:10px;">Payment</td>
            <td class="right" style="color:#64748b; font-size:10px;">{{ strtoupper($sale->payment_method) }}</td>
        </tr>
    </table>

    @if($sale->status === 'paid')
        <p class="status paid">PAYMENT RECEIVED</p>
    @elseif($sale->status === 'voided')
        <p class="status voided">VOIDED</p>
    @endif

    @if($sale->notes)
        <p style="text-align:center; color:#64748b; font-size:10px;">{{ $sale->notes }}</p>
    @endif

    <p class="footer">Thank you for your business.</p>
</body>
</html>
