@php
    $statusVariant = match($sale->status) {
        'paid' => 'positive',
        'voided' => 'negative',
        default => null,
    };
@endphp
<x-document-layout
    doc-type="Receipt"
    :reference="$sale->reference"
    date-label="Date"
    :date="$sale->paid_at?->format('d M Y, H:i') ?? '—'"
    :status-label="$sale->status === 'paid' ? 'Payment Received' : ($sale->status === 'voided' ? 'Voided' : null)"
    :status-variant="$statusVariant"
>
    <x-slot:parties>
        <td>
            <div class="label">Customer</div>
            <div class="name">{{ $sale->customer?->name ?? 'Walk-in' }}</div>
        </td>
        <td class="party-right">
            <div class="label">Served By</div>
            <div class="line">{{ $sale->appointment?->staff?->name ?? '—' }}</div>
        </td>
    </x-slot:parties>

    <h3 class="section-heading">Items</h3>
    <table class="doc-table num">
        <thead>
            <tr>
                <th>Item</th>
                <th class="num-col">Qty</th>
                <th class="num-col">Price</th>
                <th class="num-col">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->name }} ({{ ucfirst($item->item_type) }})</td>
                    <td class="num-col">{{ $item->quantity }}</td>
                    <td class="num-col">R{{ number_format($item->unit_price, 2) }}</td>
                    <td class="num-col">R{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <x-slot:resolution>
        <div class="totals-wrap">
            <table class="totals num">
                <tr><td>Subtotal</td><td class="num-col">R{{ number_format($sale->subtotal, 2) }}</td></tr>
                @if($sale->discount_amount > 0)
                    <tr><td>Discount</td><td class="num-col">−R{{ number_format($sale->discount_amount, 2) }}</td></tr>
                @endif
                <tr class="t-rule t-final">
                    <td class="k">Total</td>
                    <td class="num-col v">R{{ number_format($sale->total, 2) }}</td>
                </tr>
                <tr><td colspan="2" class="num-col">Paid via {{ strtoupper($sale->payment_method) }}</td></tr>
            </table>
        </div>

        @if($sale->notes)
            <p style="margin-top:12px; font-size:9.5px; color:#4B5563;">{{ $sale->notes }}</p>
        @endif
    </x-slot:resolution>
</x-document-layout>
