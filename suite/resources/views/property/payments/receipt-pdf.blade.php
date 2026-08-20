@php
    $tenant = \App\Models\Tenant::find($rentPayment->tenant_id);
    $property = $rentPayment->lease?->property ?? $rentPayment->unit?->property;
    $lessorName  = $property?->owner_name ?: $tenant?->name;
    $lessorEmail = $property?->owner_email ?: $tenant?->email;
    $lessorPhone = $property?->owner_phone ?: $tenant?->phone;

    $logoAbsolutePath = $tenant?->logo_url ? storage_path('app/public/' . ltrim($tenant->logo_url, '/')) : null;
@endphp
<x-document-layout
    doc-type="Payment Receipt"
    :reference="'RCT-' . str_pad($rentPayment->id, 6, '0', STR_PAD_LEFT)"
    date-label="Paid On"
    :date="$rentPayment->paid_date?->format('d M Y') ?? '—'"
    :status-label="$rentPayment->status === 'partial' ? 'Partial' : 'Paid'"
    :status-variant="'positive'"
    :company-name="$lessorName ?: config('app.name')"
    :company-addr="$property?->address_line_1"
    :company-vat="$tenant?->vat_number"
    :company-email="$lessorEmail"
    :company-phone="$lessorPhone"
    :logo-absolute-path="$logoAbsolutePath"
>
    <x-slot:parties>
        <td>
            <div class="label">Received By</div>
            <div class="name">{{ $lessorName ?: '—' }}</div>
            @if($lessorEmail)<div class="line">{{ $lessorEmail }}</div>@endif
        </td>
        <td class="party-right">
            <div class="label">Received From</div>
            <div class="name">{{ $rentPayment->renter?->name ?? '—' }}</div>
            @if($rentPayment->renter?->email)<div class="line">{{ $rentPayment->renter->email }}</div>@endif
        </td>
    </x-slot:parties>

    <table class="doc-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num-col">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Rent — {{ $rentPayment->periodLabel() }}<br>
                    <span style="color:#9CA3AF;">{{ $property?->name }} — Unit {{ $rentPayment->unit?->unit_number }}</span>
                </td>
                <td class="num num-col">R{{ number_format($rentPayment->amount_paid, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <x-slot:resolution>
        <div class="totals-wrap">
            <table class="totals num">
                <tr><td>Amount Due</td><td class="num-col">R{{ number_format($rentPayment->amount_due, 2) }}</td></tr>
                <tr class="t-rule t-final">
                    <td class="k">Amount Paid</td>
                    <td class="num-col v">R{{ number_format($rentPayment->amount_paid, 2) }}</td>
                </tr>
                @if($rentPayment->status === 'partial')
                    <tr><td>Balance Remaining</td><td class="num-col">R{{ number_format($rentPayment->amount_due - $rentPayment->amount_paid, 2) }}</td></tr>
                @endif
            </table>
        </div>
        @if($rentPayment->payment_method || $rentPayment->reference)
            <p style="font-size: 9.5px; color: #4B5563; margin-top: 10px;">
                @if($rentPayment->payment_method)Method: {{ strtoupper($rentPayment->payment_method) }}@endif
                @if($rentPayment->reference) &middot; Ref: {{ $rentPayment->reference }}@endif
            </p>
        @endif
    </x-slot:resolution>
</x-document-layout>
