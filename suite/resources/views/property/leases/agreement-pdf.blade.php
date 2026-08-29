<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lease Agreement — Lease #{{ $lease->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #1f2937;
            background: #fff;
        }
        .serif { font-family: "DejaVu Serif", Georgia, serif; }
        .page { padding: 22px 32px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; }
        .brand-name { font-size: 15px; font-weight: 700; color: #0B2D5B; }
        .doc-block { text-align: right; }
        .doc-title { font-size: 13px; font-weight: 700; letter-spacing: 2px; color: #0B2D5B; text-transform: uppercase; margin-bottom: 5px; }
        .info-matrix { border-collapse: collapse; margin-left: auto; }
        .info-matrix td { padding: 1px 0; font-size: 9px; }
        .info-matrix .k { color: #6b7280; padding-right: 14px; white-space: nowrap; text-align: left; }
        .info-matrix .v { color: #1f2937; font-weight: 700; text-align: right; white-space: nowrap; }

        .rule-gold { border: none; border-top: 1.5px solid #C89B3C; margin: 10px 0; }

        .parties { display: flex; justify-content: space-between; gap: 32px; margin: 12px 0; }
        .party { flex: 1; }
        .party .label { font-size: 8px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .party .name { font-size: 11px; font-weight: 700; color: #0B2D5B; margin-bottom: 2px; }
        .party .line { font-size: 9px; color: #374151; line-height: 1.5; }
        .party.right { text-align: right; }

        .section { margin-top: 14px; page-break-inside: avoid; }
        .section-num { font-size: 10.5px; font-weight: 700; color: #0B2D5B; }
        .section-title { font-size: 10.5px; font-weight: 700; color: #0B2D5B; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #C89B3C; padding-bottom: 3px; margin-bottom: 6px; }
        .section p { margin-bottom: 4px; color: #1f2937; }
        .section ul { margin: 4px 0 4px 16px; padding: 0; }
        .section li { margin-bottom: 3px; color: #1f2937; }
        .fact { color: #0B2D5B; font-weight: 700; }

        .summary { width: 100%; border-collapse: collapse; margin: 4px 0; }
        .summary td { padding: 2.5px 0; font-size: 9.5px; }
        .summary .k { color: #6b7280; width: 45%; }
        .summary .v { color: #1f2937; font-weight: 700; }

        .signatures { margin-top: 26px; page-break-inside: avoid; }
        .sig-row { display: flex; justify-content: space-between; gap: 40px; margin-top: 30px; }
        .sig-block { flex: 1; }
        .sig-line { border-top: 1px solid #1f2937; padding-top: 4px; font-size: 9px; color: #6b7280; }
        .sig-name { font-size: 9.5px; color: #1f2937; font-weight: 700; margin-bottom: 22px; }

        .footer-meta { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 20px; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="brand-name serif">{{ $lease->property?->name }}</div>
        <div class="doc-block">
            <div class="doc-title serif">Lease Agreement</div>
            <table class="info-matrix">
                <tr><td class="k">Lease</td><td class="v">#{{ $lease->id }}</td></tr>
                <tr><td class="k">Date Signed</td><td class="v">{{ ($lease->signed_date ?? $lease->start_date)?->format('d M Y') }}</td></tr>
            </table>
        </div>
    </div>

    <hr class="rule-gold">

    <p style="font-size:9.5px;color:#374151;">This Lease Agreement is entered into by and between the Lessor and the Lessee named below, on the terms and conditions set out in this document.</p>

    <div class="parties">
        <div class="party">
            <div class="label">Lessor</div>
            <div class="name">{{ $lease->property?->owner_name ?? '—' }}</div>
            @if($lease->property?->owner_id_number)<div class="line">Identity Number: {{ $lease->property->owner_id_number }}</div>@endif
            @if($lease->property?->owner_phone)<div class="line">Cell: {{ $lease->property->owner_phone }}</div>@endif
            @if($lease->property?->owner_email)<div class="line">Email: {{ $lease->property->owner_email }}</div>@endif
        </div>
        <div class="party right">
            <div class="label">Lessee</div>
            <div class="name">{{ $lease->renter?->name ?? '—' }}</div>
            @if($lease->renter?->id_number)<div class="line">Identity Number: {{ $lease->renter->id_number }}</div>@endif
            @if($lease->renter?->phone)<div class="line">Cell: {{ $lease->renter->phone }}</div>@endif
            @if($lease->renter?->email)<div class="line">Email: {{ $lease->renter->email }}</div>@endif
        </div>
    </div>

    <hr class="rule-gold">

    <div class="section">
        <div class="section-title">1. Property Details</div>
        <p>
            Section / Unit <span class="fact">{{ $lease->unit?->unit_number }}</span>,
            <span class="fact">{{ $lease->property?->name }}</span>,
            {{ $lease->property?->address_line_1 }}{{ $lease->property?->address_line_2 ? ', ' . $lease->property->address_line_2 : '' }},
            {{ $lease->property?->city }}{{ $lease->property?->postal_code ? ', ' . $lease->property->postal_code : '' }}.
            @if($lease->unit?->parking_bay)
                Includes parking bay number <span class="fact">{{ $lease->unit->parking_bay }}</span>.
            @endif
        </p>
    </div>

    <div class="section">
        <div class="section-title">2. Lease Period</div>
        <p>
            @if($lease->end_date)
                Fixed period from <span class="fact">{{ $lease->start_date->format('d F Y') }}</span> to <span class="fact">{{ $lease->end_date->format('d F Y') }}</span>.
            @else
                Month-to-month, commencing <span class="fact">{{ $lease->start_date->format('d F Y') }}</span>.
            @endif
            The Lessee must vacate or give notice to renew at least one month prior to the end of the lease.
        </p>
    </div>

    <div class="section">
        <div class="section-title">3. Rent and Deposit</div>
        <table class="summary">
            <tr><td class="k">Rent</td><td class="v">R{{ number_format($lease->monthly_rent, 2) }} per month</td></tr>
            <tr><td class="k">Deposit</td><td class="v">R{{ number_format($lease->deposit_amount, 2) }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">4. Renewal Option</div>
        <p>The Lessor may offer renewal for a further period, with the option provided at least one month before termination. The Lessee must give one month's written notice to accept renewal.</p>
    </div>

    <div class="section">
        <div class="section-title">5. Payment Terms</div>
        <p>Rent is payable monthly in advance on the first day of each month, without deduction.</p>
    </div>

    <div class="section">
        <div class="section-title">6. Lessee Obligations</div>
        <ul>
            <li>Pay all electricity charges.</li>
            <li>Not cede, assign, or sublet without the Lessor's written consent.</li>
            <li>Use the property for residential purposes only, unless otherwise agreed.</li>
            <li>Keep the property clean and habitable.</li>
            <li>Make no structural changes without the Lessor's consent.</li>
            <li>Allow inspections with reasonable notice.</li>
            <li>Adhere to Body Corporate Rules and pay any fines arising from a breach of them.</li>
            <li>Maintain and repair the interior, fair wear and tear excepted.</li>
            <li>Avoid causing noise or nuisance to neighbours.</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">7. Lessor Obligations</div>
        <ul>
            <li>Maintain the exterior and roof of the property.</li>
            <li>Not liable for damage arising from leaks, weather, or utility interruptions beyond the Lessor's reasonable control.</li>
            <li>Pay all levies assessed on the property.</li>
            <li>May require the Lessee to reinstate the property to its original condition at the end of the lease.</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">8. Destruction or Damage</div>
        <p>The Lessor may terminate this lease if the property is destroyed. The Lessee is entitled to a rent abatement if the property becomes unfit for occupation, unless this is due to the Lessee's fault.</p>
    </div>

    <div class="section">
        <div class="section-title">9. Breach and Cancellation</div>
        <p>The Lessor may cancel this lease if the Lessee defaults and fails to remedy the default within 3 days of written notice. If a default is disputed, the Lessee must continue paying rent until the dispute is resolved.</p>
    </div>

    <div class="section">
        <div class="section-title">10. Sale of Property</div>
        <p>The Lessor may sell the property with one month's written notice to the Lessee. The Lessee must allow reasonable access for viewings.</p>
    </div>

    <div class="section">
        <div class="section-title">11. Notices</div>
        <p>Notices are deemed valid if sent by registered letter or delivered by hand to the addresses recorded in this agreement.</p>
    </div>

    <div class="section">
        <div class="section-title">12. Legal Jurisdiction</div>
        <p>The Lessee consents to the jurisdiction of the Magistrate's Court in respect of any dispute arising from this agreement.</p>
    </div>

    <div class="section">
        <div class="section-title">13. Variation</div>
        <p>Any changes to this agreement must be in writing and signed by both parties.</p>
    </div>

    <div class="section">
        <div class="section-title">14. Special Conditions</div>
        <ul>
            <li>The deposit is refundable after the lease expires, less deductions for outstanding accounts or damages.</li>
            <li>The deposit may not be used as rent for any month.</li>
        </ul>
    </div>

    <div class="signatures">
        <p style="font-size:9.5px;color:#374151;">
            Signed by the Lessor and the Lessee at {{ $lease->property?->name }} on {{ ($lease->signed_date ?? $lease->start_date)?->format('d/m/Y') }}.
        </p>
        <div class="sig-row">
            <div class="sig-block">
                <div class="sig-name">&nbsp;</div>
                <div class="sig-line">Lessor — {{ $lease->property?->owner_name ?? '—' }}</div>
            </div>
            <div class="sig-block">
                <div class="sig-name">&nbsp;</div>
                <div class="sig-line">Lessee — {{ $lease->renter?->name ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="footer-meta">Generated {{ now()->format('d M Y H:i') }} &middot; Lease Agreement #{{ $lease->id }}</div>

</div>
</body>
</html>
