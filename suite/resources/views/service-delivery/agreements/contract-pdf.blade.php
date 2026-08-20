@php
    $statusVariant = match($serviceAgreement->status) {
        'active' => 'positive',
        'suspended', 'terminated' => 'negative',
        default => null,
    };
@endphp
<x-document-layout
    doc-type="Service Agreement"
    :reference="'SA-' . str_pad($serviceAgreement->id, 5, '0', STR_PAD_LEFT)"
    date-label="Start Date"
    :date="$serviceAgreement->start_date->format('d M Y')"
    second-date-label="Minimum Term"
    :second-date="$serviceAgreement->commitment_months . ' months'"
    :status-label="ucfirst($serviceAgreement->status)"
    :status-variant="$statusVariant"
>
    <x-slot:parties>
        <td>
            <div class="label">Client</div>
            <div class="name">{{ $serviceAgreement->client->name }}</div>
            @if($serviceAgreement->client->email)<div class="line">{{ $serviceAgreement->client->email }}</div>@endif
            @if($serviceAgreement->client->phone)<div class="line">{{ $serviceAgreement->client->phone }}</div>@endif
        </td>
        <td class="party-right">
            <div class="label">Plan</div>
            <div class="name">{{ $serviceAgreement->plan_name }}</div>
            <div class="line">R{{ number_format($serviceAgreement->monthly_fee, 2) }} / month · billed day {{ $serviceAgreement->billing_day }}</div>
        </td>
    </x-slot:parties>

    <table class="doc-table" style="margin-bottom: 14px;">
        <tbody>
            <tr>
                <td style="width:25%; color:#9CA3AF; font-size:8px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">Minor Changes Included</td>
                <td style="font-weight:600;">{{ $serviceAgreement->minutes_allowance }} minutes / month</td>
                <td style="width:25%; color:#9CA3AF; font-size:8px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">Reactivation Fee</td>
                <td style="font-weight:600;">R{{ number_format($serviceAgreement->reactivation_fee, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size:9.5px; color:#4B5563; margin-bottom:14px;">
        <strong>Service Provider:</strong> {{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }}
        &nbsp;·&nbsp; <strong>Client:</strong> the party named above receiving hosting and maintenance services under this plan
        &nbsp;·&nbsp; <strong>Services:</strong> website hosting, monitoring, and administrative support as defined below.
    </p>

    {{-- SECTION A --}}
    <h3 class="section-heading" style="border-bottom:1px solid #C89B3C; padding-bottom:4px; margin-top:18px;">Section A — Service Level Agreement (SLA)</h3>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">2. Scope of Services</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:6px;">Under the {{ $serviceAgreement->plan_name }} plan, the Service Provider agrees to provide:</p>
    <p style="font-weight:600; font-size:9.5px; margin-bottom:3px;">2.1 Hosting Services</p>
    <ul style="margin:0 0 8px 16px; font-size:9.5px; color:#4B5563;">
        <li>Website hosting</li>
        <li>SSL certificate (if applicable)</li>
        <li>Server uptime monitoring</li>
        <li>Monthly backup verification</li>
    </ul>
    <p style="font-weight:600; font-size:9.5px; margin-bottom:3px;">2.2 Maintenance Services</p>
    <ul style="margin:0 0 8px 16px; font-size:9.5px; color:#4B5563;">
        <li>Website security updates</li>
        <li>Plugin / module updates (where applicable)</li>
        <li>Basic performance checks</li>
    </ul>
    <p style="font-weight:600; font-size:9.5px; margin-bottom:3px;">2.3 Minor Administrative Changes (Limited)</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">The monthly maintenance plan includes minor updates such as text edits, address or contact detail changes, adding or removing email accounts, and updating images supplied by the Client. These changes are limited to a maximum of <strong>{{ $serviceAgreement->minutes_allowance }} minutes per month</strong>. Unused time does not roll over.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">3. Exclusions</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">The following services are not included unless separately quoted: website redesign, new page development, custom functionality or integrations, e-commerce modifications, SEO campaigns, digital marketing services, content creation, and emergency after-hours support. Any work outside the defined scope will be quoted at the standard hourly rate.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">4. Response Times</p>
    <table class="doc-table" style="margin-bottom:6px;">
        <tbody>
            <tr><td style="width:35%; color:#4B5563; font-weight:600;">Critical Downtime</td><td style="color:#4B5563;">Response within 24 business hours</td></tr>
            <tr><td style="color:#4B5563; font-weight:600;">Minor Changes / Support</td><td style="color:#4B5563;">Response within 48 business hours</td></tr>
            <tr><td style="color:#4B5563; font-weight:600;">New Feature Requests</td><td style="color:#4B5563;">Subject to quotation and scheduling</td></tr>
        </tbody>
    </table>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">Business hours are Monday to Friday, excluding public holidays in South Africa.</p>

    {{-- SECTION B --}}
    <h3 class="section-heading" style="border-bottom:1px solid #C89B3C; padding-bottom:4px; margin-top:18px;">Section B — Payment Policy</h3>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">5. Billing Structure</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">Services are billed monthly in advance. Payment is due on or before day {{ $serviceAgreement->billing_day }} of each month. All fees are exclusive of VAT unless otherwise stated. Failure to pay by the due date will trigger the overdue process below.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">6. Late Payment Policy</p>
    <table class="doc-table" style="margin-bottom:10px;">
        <tbody>
            <tr><td style="width:20%; color:#4B5563; font-weight:600;">Day 3</td><td style="color:#4B5563;">First reminder issued</td></tr>
            <tr><td style="color:#4B5563; font-weight:600;">Day 7</td><td style="color:#4B5563;">Final notice issued</td></tr>
            <tr><td style="color:#4B5563; font-weight:600;">Day 10</td><td style="color:#4B5563;">Service suspension</td></tr>
            <tr><td style="color:#4B5563; font-weight:600;">Day 30</td><td style="color:#4B5563;">Account termination</td></tr>
        </tbody>
    </table>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">7. Suspension</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">If an account is suspended due to non-payment, website hosting may be temporarily disabled, email services may be suspended, and administrative access may be restricted. A reactivation fee of R{{ number_format($serviceAgreement->reactivation_fee, 2) }} will apply before services are restored — see the plan details above.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">8. Interest on Overdue Accounts</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">The Service Provider reserves the right to charge interest on overdue amounts at the maximum rate permitted by law in South Africa.</p>

    {{-- SECTION C --}}
    <h3 class="section-heading" style="border-bottom:1px solid #C89B3C; padding-bottom:4px; margin-top:18px;">Section C — Term &amp; Termination</h3>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">9. Minimum Commitment Period</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">Unless otherwise agreed in writing, services are subject to a minimum commitment period of <strong>{{ $serviceAgreement->commitment_months }} months</strong> from {{ $serviceAgreement->start_date->format('d M Y') }}. If the Client cancels before the end of the commitment period, the remaining balance of the term becomes immediately payable.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">10. Termination by Client</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">The Client may terminate services with 30 days written notice, provided all outstanding fees are settled in full and any minimum commitment balance is paid. No refunds will be issued for partial months.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">11. Termination by Service Provider</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">The Service Provider may terminate services for non-payment, breach of agreement, misuse or illegal activity, or if services become commercially unviable. Termination due to non-payment may occur immediately after the 30-day overdue period.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">12. Data Release</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">Upon full settlement of all outstanding fees, website files and database backups may be released to the Client upon written request. Transfer assistance may be billed separately. No data will be released while outstanding amounts remain unpaid.</p>

    {{-- SECTION D --}}
    <h3 class="section-heading" style="border-bottom:1px solid #C89B3C; padding-bottom:4px; margin-top:18px;">Section D — General Conditions</h3>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">13. Limitation of Liability</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">The Service Provider shall not be liable for business losses, indirect or consequential damages, downtime caused by third-party hosting providers, or cyber incidents beyond reasonable control.</p>

    <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">14. Force Majeure</p>
    <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">The Service Provider shall not be held responsible for failure to perform due to events beyond reasonable control, including but not limited to natural disasters, infrastructure failures, or government restrictions.</p>

    @if($serviceAgreement->notes)
        <p style="font-weight:600; font-size:9.5px; margin:12px 0 4px;">15. Additional Notes</p>
        <p style="font-size:9.5px; color:#4B5563; margin-bottom:10px;">{{ $serviceAgreement->notes }}</p>
    @endif

    <x-slot:resolution>
        @if($serviceAgreement->accepted_at)
            <p style="font-size:9.5px; color:#2F5D3A; font-weight:600;">
                Accepted by {{ $serviceAgreement->accepted_name }} on {{ $serviceAgreement->accepted_at->format('d F Y') }}.
            </p>
        @else
            <table class="sig-block">
                <tr>
                    <td>
                        <div class="sig-line"></div>
                        <div class="sig-label">Client Name</div>
                        <div class="sig-line" style="margin-top:10px;"></div>
                        <div class="sig-label">Company</div>
                        <div class="sig-line" style="margin-top:10px;"></div>
                        <div class="sig-label">Signature &amp; Date</div>
                    </td>
                    <td class="sig-right">
                        <div class="sig-line"></div>
                        <div class="sig-label">Service Provider — {{ \App\Models\BillingSetting::get('company_name') ?: config('app.name') }}</div>
                        <div class="sig-line" style="margin-top:10px;"></div>
                        <div class="sig-label">Signature &amp; Date</div>
                    </td>
                </tr>
            </table>
        @endif
    </x-slot:resolution>
</x-document-layout>
