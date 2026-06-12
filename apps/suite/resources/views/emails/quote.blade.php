<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote {{ $quote->reference }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 0; color: #18181b; }
        .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 36px 40px; color: #fff; }
        .header h1 { margin: 0 0 6px; font-size: 20px; font-weight: 700; }
        .header p  { margin: 0; opacity: .8; font-size: 14px; }
        .body { padding: 32px 40px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f4f4f5; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #71717a; }
        td { padding: 10px 12px; border-bottom: 1px solid #f4f4f5; font-size: 13px; }
        .text-right { text-align: right; }
        .total-row td { font-weight: 700; font-size: 15px; border-bottom: none; }
        .deposit { background: #eef2ff; border-radius: 10px; padding: 20px 24px; text-align: center; margin: 24px 0; }
        .deposit p { margin: 0; }
        .deposit .amount { font-size: 28px; font-weight: 800; color: #4f46e5; margin: 6px 0; }
        .btn { display: inline-block; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; text-decoration: none; }
        .btn-accept  { background: #4f46e5; color: #fff !important; margin-right: 10px; }
        .btn-decline { background: #f4f4f5; color: #71717a !important; }
        .footer { background: #fafafa; border-top: 1px solid #f4f4f5; padding: 20px 40px; text-align: center; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>{{ $quote->title }}</h1>
        <p>Quotation {{ $quote->reference }}
            @if ($quote->valid_until) · Valid until {{ $quote->valid_until->format('d F Y') }} @endif
        </p>
    </div>

    <div class="body">
        <p style="color:#3f3f46;line-height:1.6;margin:0 0 20px;">
            Please find your quote below. Review the details and use the buttons at the bottom to accept or decline.
        </p>

        <table class="summary-on-mobile">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote->line_items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-right">{{ $item['qty'] }} {{ $item['unit'] ?? '' }}</td>
                    <td class="text-right">R{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="text-right">R{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if ($quote->tax_rate > 0)
                <tr><td colspan="3" class="text-right" style="color:#71717a;font-size:12px;">VAT ({{ $quote->tax_rate }}%)</td><td class="text-right">R{{ number_format($quote->tax_amount, 2) }}</td></tr>
                @endif
                <tr class="total-row"><td colspan="3" class="text-right">Total</td><td class="text-right">R{{ number_format($quote->total, 2) }}</td></tr>
            </tfoot>
        </table>

        <div class="deposit">
            <p style="color:#4f46e5;font-weight:600;font-size:14px;">Deposit required to confirm ({{ $quote->deposit_percentage }}%)</p>
            <p class="amount">R{{ number_format($quote->depositAmount(), 2) }}</p>
            <p style="font-size:12px;color:#6366f1;">Balance of R{{ number_format($quote->total - $quote->depositAmount(), 2) }} due on delivery / event date.</p>
        </div>

        @if ($quote->notes)
        <p style="color:#52525b;font-size:13px;line-height:1.6;margin-bottom:24px;">{{ $quote->notes }}</p>
        @endif

        <div style="text-align:center;margin-top:28px;">
            <a href="{{ $acceptUrl }}"  class="btn btn-accept">Accept &amp; Pay Deposit →</a>
            <a href="{{ $declineUrl }}" class="btn btn-decline">Decline</a>
        </div>

        <p style="font-size:11px;color:#a1a1aa;text-align:center;margin-top:20px;">
            This link is unique to you. Do not share it.
        </p>
    </div>

    <div class="footer">
        © {{ date('Y') }} Xquisite Technologies (Pty) Ltd &mdash; One platform. Every operation.
    </div>
</div>
</body>
</html>
