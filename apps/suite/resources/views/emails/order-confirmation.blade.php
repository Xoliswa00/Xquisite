<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmed — {{ $order->reference }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; padding: 24px 16px; }
    .wrapper { max-width: 560px; margin: 0 auto; }
    .header { background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 16px 16px 0 0; padding: 32px 32px 28px; text-align: center; }
    .header-icon { width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; }
    .header h1 { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,0.8); font-size: 13px; }
    .body { background: white; padding: 32px; }
    .reference-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; text-align: center; margin-bottom: 24px; }
    .reference-box .label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .reference-box .ref { font-family: monospace; font-size: 20px; font-weight: 700; color: #065f46; }
    h2 { font-size: 13px; font-weight: 600; color: #111827; margin-bottom: 12px; }
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .items-table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 500; text-align: left; padding: 0 0 8px 0; border-bottom: 1px solid #f3f4f6; }
    .items-table th:last-child { text-align: right; }
    .items-table td { padding: 10px 0; border-bottom: 1px solid #f9fafb; font-size: 13px; color: #374151; vertical-align: top; }
    .items-table td:last-child { text-align: right; font-weight: 600; color: #111827; white-space: nowrap; }
    .item-name { font-weight: 500; color: #111827; }
    .item-meta { font-size: 11px; color: #9ca3af; margin-top: 2px; }
    .totals { margin-bottom: 24px; }
    .totals-row { display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; padding: 4px 0; }
    .totals-row.total { border-top: 2px solid #f3f4f6; margin-top: 8px; padding-top: 12px; font-size: 15px; font-weight: 700; color: #111827; }
    .totals-row.total span:last-child { color: #4f46e5; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
    .info-card { background: #f9fafb; border-radius: 10px; padding: 14px; }
    .info-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
    .info-value { font-size: 13px; font-weight: 500; color: #111827; }
    .info-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
    .eft-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 16px; margin-bottom: 24px; }
    .eft-box h3 { font-size: 13px; font-weight: 600; color: #1e40af; margin-bottom: 8px; }
    .eft-box p { font-size: 13px; color: #1e3a8a; line-height: 1.5; }
    .cta { text-align: center; margin-bottom: 24px; }
    .cta a { display: inline-block; background: #4f46e5; color: white; text-decoration: none; font-size: 14px; font-weight: 600; padding: 14px 32px; border-radius: 12px; }
    .footer { background: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 16px 16px; padding: 20px 32px; text-align: center; }
    .footer p { font-size: 12px; color: #94a3b8; line-height: 1.6; }
    .footer strong { color: #64748b; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-icon">
            <svg width="28" height="28" fill="none" stroke="white" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1>Order Confirmed!</h1>
        <p>Thank you for your order, {{ $order->customer_name }}</p>
    </div>

    <div class="body">
        <div class="reference-box">
            <div class="label">Order Reference</div>
            <div class="ref">{{ $order->reference }}</div>
        </div>

        <h2>Your Items</h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        @if($item->product_sku)
                            <div class="item-meta">SKU: {{ $item->product_sku }}</div>
                        @endif
                    </td>
                    <td>× {{ $item->quantity }}</td>
                    <td>R{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>R{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->shipping_cost > 0)
            <div class="totals-row">
                <span>Shipping</span>
                <span>R{{ number_format($order->shipping_cost, 2) }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total</span>
                <span>R{{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">Fulfillment</div>
                <div class="info-value" style="text-transform:capitalize">{{ $order->fulfillment_type }}</div>
                @if($order->fulfillment_type === 'delivery' && $order->shipping_address)
                    <div class="info-sub">{{ $order->shipping_address['line1'] }}, {{ $order->shipping_address['city'] }}</div>
                @endif
            </div>
            <div class="info-card">
                <div class="info-label">Payment</div>
                <div class="info-value">
                    @if($order->payment_method === 'payfast') Online
                    @elseif($order->payment_method === 'eft') Bank Transfer
                    @else On Collection
                    @endif
                </div>
                <div class="info-sub" style="{{ $order->payment_status === 'paid' ? 'color:#059669' : 'color:#d97706' }}">
                    {{ $order->payment_status === 'paid' ? 'Paid' : 'Awaiting payment' }}
                </div>
            </div>
        </div>

        @if($order->payment_method === 'eft')
        <div class="eft-box">
            <h3>EFT Payment Instructions</h3>
            <p>Please make your payment using order reference <strong>{{ $order->reference }}</strong>. Your order will be processed as soon as payment reflects in our account.</p>
        </div>
        @endif

        @if($order->notes)
        <div style="background:#f9fafb;border-radius:10px;padding:14px;margin-bottom:24px;">
            <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px;">Your Notes</div>
            <p style="font-size:13px;color:#374151;">{{ $order->notes }}</p>
        </div>
        @endif

        <div class="cta">
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Keep an eye on your email — we'll notify you when your order status changes.</p>
        </div>
    </div>

    <div class="footer">
        <p>
            Questions? Contact us at <strong>{{ $tenant->email }}</strong><br>
            © {{ date('Y') }} <strong>{{ $tenant->name }}</strong> · Powered by <strong>Xquisite Suite</strong>
        </p>
    </div>
</div>
</body>
</html>
