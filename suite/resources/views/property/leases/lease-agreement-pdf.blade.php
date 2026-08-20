@php
    $tenant = \App\Models\Tenant::find($lease->tenant_id);
    $lessorName  = $lease->property?->owner_name ?: $tenant?->name;
    $lessorEmail = $lease->property?->owner_email ?: $tenant?->email;
    $lessorPhone = $lease->property?->owner_phone ?: $tenant?->phone;

    $logoAbsolutePath = $tenant?->logo_url ? storage_path('app/public/' . ltrim($tenant->logo_url, '/')) : null;

    $statusVariant = match ($lease->status) {
        'active' => 'positive',
        'terminated' => 'negative',
        default => null,
    };

    $propertyDescription = collect([$lease->property?->name, $lease->property?->address_line_1, $lease->property?->city])
        ->filter()->implode(', ');
@endphp
<x-document-layout
    doc-type="Lease Agreement"
    :reference="'LEASE-' . str_pad($lease->id, 5, '0', STR_PAD_LEFT)"
    date-label="Start Date"
    :date="$lease->start_date->format('d M Y')"
    second-date-label="End Date"
    :second-date="$lease->end_date?->format('d M Y') ?? 'Month-to-month'"
    :status-label="ucfirst($lease->status)"
    :status-variant="$statusVariant"
    :company-name="$lessorName ?: config('app.name')"
    :company-addr="$lease->property?->address_line_1"
    :company-vat="$tenant?->vat_number"
    :company-email="$lessorEmail"
    :company-phone="$lessorPhone"
    :logo-absolute-path="$logoAbsolutePath"
>
    <x-slot:parties>
        <td>
            <div class="label">Lessor</div>
            <div class="name">{{ $lessorName ?: '—' }}</div>
            @if($lessorEmail)<div class="line">{{ $lessorEmail }}</div>@endif
            @if($lessorPhone)<div class="line">{{ $lessorPhone }}</div>@endif
        </td>
        <td class="party-right">
            <div class="label">Lessee</div>
            <div class="name">{{ $lease->renter?->name ?? '—' }}</div>
            @if($lease->renter?->email)<div class="line">{{ $lease->renter->email }}</div>@endif
            @if($lease->renter?->phone)<div class="line">{{ $lease->renter->phone }}</div>@endif
            @if($lease->renter?->id_number)<div class="line">ID {{ $lease->renter->id_number }}</div>@endif
        </td>
    </x-slot:parties>

    <h3 class="section-heading">Premises</h3>
    <table class="doc-table" style="margin-bottom: 14px;">
        <tbody>
            <tr>
                <td style="font-weight: 600; width: 30%;">Property</td>
                <td>{{ $propertyDescription }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Unit</td>
                <td>Unit {{ $lease->unit?->unit_number }} ({{ ucfirst($lease->unit?->type ?? '') }})</td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-heading">Lease Terms</h3>
    <table class="doc-table">
        <tbody>
            <tr>
                <td style="font-weight: 600; width: 30%;">Monthly Rent</td>
                <td class="num">R{{ number_format($lease->monthly_rent, 2) }}, due on the 1st of each month</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Deposit</td>
                <td class="num">R{{ number_format($lease->deposit_amount, 2) }} — {{ $lease->deposit_paid ? 'paid' : 'outstanding' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Term</td>
                <td>{{ $lease->start_date->format('d M Y') }} to {{ $lease->end_date?->format('d M Y') ?? 'month-to-month, until terminated by either party' }}</td>
            </tr>
        </tbody>
    </table>

    @if($lease->notes)
        <h3 class="section-heading" style="margin-top: 14px;">Additional Terms</h3>
        <p style="font-size: 9.5px; color: #4B5563; line-height: 1.5;">{{ $lease->notes }}</p>
    @endif

    <x-slot:resolution>
        <p style="font-size: 9.5px; color: #4B5563; line-height: 1.5; margin-bottom: 4px;">
            The Lessee agrees to occupy the premises under the terms above, to pay rent on time, and to maintain the
            unit in good condition. The Lessor agrees to maintain the property in a habitable state and to return
            the deposit, less any deductions for damage beyond normal wear, within a reasonable period after the
            lease ends.
        </p>
        <table class="sig-block">
            <tr>
                <td>
                    <div class="sig-line"></div>
                    <div class="sig-label">Lessor Signature &amp; Date</div>
                </td>
                <td class="sig-right">
                    <div class="sig-line"></div>
                    <div class="sig-label">Lessee Signature &amp; Date</div>
                </td>
            </tr>
        </table>
    </x-slot:resolution>
</x-document-layout>
