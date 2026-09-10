<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* dompdf 3.1.x: @page margin has no effect in this install — page margins
           are set on <body>. @page keeps size only. Two-column zones are real
           <table>/<td>, not flex (dompdf flex overflows a right column past the
           page edge). To move to Libre Caslon Text + Public Sans, drop the two
           .ttf into resources/fonts/documents/, add @font-face here, and change
           the `.serif` / body font-family stacks — nothing else changes. */
        @page { size: A4; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1A1A1A;
            background: #fff;
            margin: 42px 46px;
        }
        .serif { font-family: "DejaVu Serif", Georgia, serif; }
        .num { font-variant-numeric: tabular-nums; }

        .rule-gold { border: none; border-top: 1.5px solid #C89B3C; height: 0; }
        .rule-hair { border: none; border-top: 0.75px solid #E3E2DD; height: 0; }

        /* ── Masthead ───────────────────────────────────────── */
        table.mast { width: 100%; border-collapse: collapse; }
        table.mast td { vertical-align: top; }
        table.mast td.r { text-align: right; }
        .brand-mark { height: 56px; width: auto; display: block; margin-bottom: 10px; }
        .co-name { font-size: 16px; font-weight: 700; color: #1A1A1A; line-height: 1.15; }

        .doc-title { font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: #1A1A1A; }
        table.meta { border-collapse: collapse; margin-left: auto; margin-top: 9px; }
        table.meta td { padding: 1.5px 0; font-size: 9px; }
        table.meta td.k { color: #6b6b66; padding-right: 18px; text-align: left; white-space: nowrap; }
        table.meta td.v { font-weight: 700; text-align: right; white-space: nowrap; }
        table.meta td.v.status-overdue { color: #9A2A2A; }
        table.meta td.v.status-paid    { color: #1F6B3A; }
        table.meta td.v.status-pop     { color: #0078D4; }

        /* ── From / Bill To ─────────────────────────────────── */
        table.parties { width: 100%; border-collapse: collapse; }
        table.parties td { vertical-align: top; width: 50%; padding-right: 28px; }
        table.parties td.r { text-align: right; padding-right: 0; padding-left: 28px; }
        .p-label { font-size: 8px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: #6b6b66; padding-bottom: 5px; }
        .p-name { font-size: 11px; font-weight: 700; color: #1A1A1A; padding-bottom: 2px; }
        .p-line { font-size: 9.5px; color: #3D3D3A; line-height: 1.5; }

        /* ── Line items ─────────────────────────────────────── */
        table.items { width: 100%; border-collapse: collapse; }
        table.items th {
            text-align: left; font-size: 8px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: #1A1A1A; padding: 11px 0 6px;
            border-bottom: 1.25px solid #1A1A1A;
        }
        table.items th.r { text-align: right; }
        table.items td { font-size: 10px; color: #1A1A1A; padding: 7px 0; border-bottom: 0.75px solid #E3E2DD; vertical-align: top; }
        table.items td.r { text-align: right; white-space: nowrap; }
        table.items tr:last-child td { border-bottom: none; }
        .item-sub { color: #6b6b66; }
        .item-includes { font-size: 9px; color: #6b6b66; margin-top: 3px; }
        .col-qty { width: 46px; }
        .col-price { width: 96px; }
        .col-amt { width: 110px; }

        /* ── Summary + total band ───────────────────────────── */
        table.sum { border-collapse: collapse; margin-left: auto; margin-top: 12px; }
        table.sum td { font-size: 9.5px; padding: 2.5px 0; }
        table.sum td.k { color: #6b6b66; text-align: left; padding-right: 32px; white-space: nowrap; }
        table.sum td.v { text-align: right; white-space: nowrap; }

        /* Light band — a gold rule carries the emphasis, not a dark fill (prints
           clean, doesn't read like a demand letter). Gold is still reserved for
           this one figure + the masthead rule. */
        table.total { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.total td { background: #F3F1EC; padding: 15px 20px; vertical-align: middle; border-top: 2px solid #C89B3C; }
        .t-label { font-size: 8.5px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #6b6b66; }
        .t-sub { font-size: 9px; color: #6b6b66; margin-top: 3px; }
        .t-sub.overdue  { color: #9A2A2A; font-weight: 700; }
        .t-sub.awaiting { color: #1F6B3A; font-weight: 700; }
        td.t-figcell { text-align: right; }
        .t-fig { font-size: 25px; font-weight: 700; color: #C89B3C; white-space: nowrap; }

        /* ── Paid confirmation ──────────────────────────────── */
        .paid-line { margin-top: 12px; padding-top: 7px; border-top: 0.75px solid #E3E2DD; font-size: 9.5px; color: #1F6B3A; font-weight: 700; letter-spacing: 0.3px; }

        /* ── Payment instructions ───────────────────────────── */
        .sec-head { font-size: 8.5px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: #1A1A1A; padding-bottom: 4px; border-bottom: 1px solid #C89B3C; }
        table.pay { width: 100%; border-collapse: collapse; margin-top: 9px; }
        table.pay td { width: 50%; vertical-align: top; padding: 3px 0; }
        .pay-k { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #6b6b66; }
        .pay-v { font-size: 9.5px; font-weight: 700; color: #1A1A1A; margin-top: 1px; }
        .pay-ref { margin-top: 7px; font-size: 9.5px; color: #1A1A1A; }

        /* ── Footer ─────────────────────────────────────────── */
        table.foot { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.foot td { vertical-align: top; width: 33.33%; padding-right: 18px; }
        table.foot td.r { text-align: right; padding-right: 0; }
        .foot-k { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6b6b66; padding-bottom: 3px; }
        .foot-v { font-size: 9.5px; color: #3D3D3A; line-height: 1.4; }
        .thanks { text-align: center; font-size: 9.5px; color: #3D3D3A; margin-top: 16px; }
        .meta-line { text-align: center; font-size: 9px; color: #9A9A95; margin-top: 3px; }
    </style>
</head>
<body>

    {{-- Masthead --}}
    @php
        $companyName = \App\Models\BillingSetting::get('company_name') ?: config('app.name');
        $companyVat  = \App\Models\BillingSetting::get('company_vat');
        $companyReg  = \App\Models\BillingSetting::get('company_registration');
        $companyAddr = \App\Models\BillingSetting::get('company_address');
        $companyPhone = \App\Models\BillingSetting::get('company_phone');
        $companyEmail = \App\Models\BillingSetting::get('company_email');
        $companyWeb   = \App\Models\BillingSetting::get('company_website');
        $awaiting = $invoice->isAwaitingConfirmation();

        // "Tax Invoice" only when the issuer is VAT-registered; otherwise a plain
        // "Invoice". A compliant tax invoice needs a VAT line — that comes back
        // once generateInvoice() computes and stores VAT (no vat/discount columns
        // on platform_invoices yet, so both are omitted here rather than shown as
        // a misleading R 0.00).
        $docTitle = $companyVat ? 'Tax Invoice' : 'Invoice';

        $statusClass = match(true) {
            $awaiting                     => 'status-pop',
            $invoice->status === 'paid'   => 'status-paid',
            $invoice->status === 'overdue' => 'status-overdue',
            default                       => '',
        };
        $statusLabel = $awaiting ? 'AWAITING CONFIRMATION' : strtoupper($invoice->status_badge['label']);
        $dueDays = (int) (\App\Models\BillingSetting::get('invoice_due_days') ?? 7);
    @endphp
    <table class="mast">
        <tr>
            <td>
                <img src="{{ public_path('img/android-icon-192x192.png') }}" alt="" class="brand-mark">
                <div class="co-name serif">{{ $companyName }}</div>
            </td>
            <td class="r">
                <div class="doc-title serif">{{ $docTitle }}</div>
                <table class="meta num">
                    <tr><td class="k">Invoice No</td><td class="v">{{ $invoice->invoice_number }}</td></tr>
                    <tr><td class="k">Issue date</td><td class="v">{{ $invoice->created_at->format('d M Y') }}</td></tr>
                    <tr><td class="k">Due date</td><td class="v">{{ $invoice->due_date->format('d M Y') }}</td></tr>
                    <tr><td class="k">Terms</td><td class="v">Net {{ $dueDays }}</td></tr>
                    <tr><td class="k">Currency</td><td class="v">ZAR</td></tr>
                    <tr><td class="k">Status</td><td class="v {{ $statusClass }}">{{ $statusLabel }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <hr class="rule-gold" style="margin: 16px 0 20px;">

    {{-- From / Bill To --}}
    <table class="parties">
        <tr>
            <td>
                <div class="p-label">From</div>
                <div class="p-name">{{ $companyName }}</div>
                @if($companyAddr)<div class="p-line">{{ $companyAddr }}</div>@endif
                @if($companyReg)<div class="p-line">Reg {{ $companyReg }}</div>@endif
                @if($companyVat)<div class="p-line">VAT {{ $companyVat }}</div>@endif
                @if($companyPhone)<div class="p-line">{{ $companyPhone }}</div>@endif
                @if($companyEmail)<div class="p-line">{{ $companyEmail }}</div>@endif
            </td>
            <td class="r">
                <div class="p-label">Bill to</div>
                <div class="p-name">{{ $invoice->tenant->name }}</div>
                @if($invoice->tenant->address)<div class="p-line">{{ $invoice->tenant->address }}</div>@endif
                @if($invoice->tenant->vat_number)<div class="p-line">VAT {{ $invoice->tenant->vat_number }}</div>@endif
                @if($invoice->tenant->phone)<div class="p-line">{{ $invoice->tenant->phone }}</div>@endif
                @if($invoice->tenant->email)<div class="p-line">{{ $invoice->tenant->email }}</div>@endif
            </td>
        </tr>
    </table>

    <hr class="rule-hair" style="margin: 20px 0 0;">

    {{-- Line item. The invoice amount is a frozen snapshot taken at issue time;
         re-itemising from the tenant's *current* active modules would not
         reconcile with it once modules or prices change (it didn't — that was the
         old bug). A single subscription line for the billing period always
         balances against amount. The "Includes" note carries no prices, so it
         can't drift out of balance — and it's only shown for the current period,
         where the tenant's live modules still match what was billed. A true
         per-module priced breakdown needs the line items snapshotted onto the
         invoice at creation. --}}
    @php
        $periodLabel = ($invoice->billing_period_start && $invoice->billing_period_end)
            ? $invoice->billing_period_start->format('d M Y') . ' – ' . $invoice->billing_period_end->format('d M Y')
            : $invoice->created_at->format('F Y');

        $isCurrentPeriod = $invoice->billing_period_start && $invoice->billing_period_start->isSameMonth(now());
        $includedModules = $isCurrentPeriod
            ? $invoice->tenant->activeModules()->with('platformModule')->get()
                ->map(fn ($tm) => $tm->platformModule?->name ?? ucfirst(str_replace('_', ' ', $tm->module)))
                ->filter()->values()
            : collect();
    @endphp
    <table class="items num">
        <thead>
            <tr>
                <th>Description</th>
                <th class="r col-qty">Qty</th>
                <th class="r col-price">Unit price</th>
                <th class="r col-amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Xquisite platform subscription <span class="item-sub">— {{ $periodLabel }}</span>
                    @if($includedModules->isNotEmpty())<div class="item-includes">Includes {{ $includedModules->implode(', ') }}</div>@endif
                </td>
                <td class="r">1</td>
                <td class="r">R {{ number_format($invoice->amount, 2) }}</td>
                <td class="r">R {{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Summary — only meaningful once a payment has landed. --}}
    @php
        $isPaid = $invoice->status === 'paid';
        $paymentsReceived = $isPaid ? (float) $invoice->amount : 0.0;
        $balanceDue = (float) $invoice->amount - $paymentsReceived;
        $daysUntilDue = $invoice->days_until_due;
        $dueUrgencyText = match(true) {
            $isPaid            => null,
            $awaiting          => 'Proof of payment received, awaiting confirmation',
            $daysUntilDue < 0  => 'Overdue by ' . abs($daysUntilDue) . ' day' . (abs($daysUntilDue) === 1 ? '' : 's'),
            $daysUntilDue === 0 => 'Payable today',
            default            => 'Payable by ' . $invoice->due_date->format('d F Y'),
        };
    @endphp
    @if($paymentsReceived > 0)
        <table class="sum num">
            <tr><td class="k">Subtotal</td><td class="v">R {{ number_format($invoice->amount, 2) }}</td></tr>
            <tr><td class="k">Payments received</td><td class="v">R {{ number_format($paymentsReceived, 2) }}</td></tr>
        </table>
    @endif

    <table class="total num">
        <tr>
            <td>
                @if($isPaid)
                    <div class="t-label">Amount paid</div>
                    <div class="t-sub">Paid {{ $invoice->paid_at->format('d F Y') }}</div>
                @else
                    <div class="t-label">Total due</div>
                    @if($dueUrgencyText)
                        <div class="t-sub {{ $awaiting ? 'awaiting' : ($daysUntilDue < 0 ? 'overdue' : '') }}">{{ $dueUrgencyText }}</div>
                    @endif
                @endif
            </td>
            <td class="t-figcell">
                <span class="t-fig serif">R {{ number_format($isPaid ? $invoice->amount : $balanceDue, 2) }}</span>
            </td>
        </tr>
    </table>

    {{-- Paid confirmation — method + reference (the date is already in the band) --}}
    @if($isPaid)
        <div class="paid-line">
            PAID IN FULL
            @if($invoice->payment_method) &nbsp;·&nbsp; {{ $invoice->payment_method }}@endif
            @if($invoice->payment_reference) &nbsp;·&nbsp; Ref {{ $invoice->payment_reference }}@endif
        </div>
    @endif

    {{-- Payment instructions — hidden once proof of payment is in (awaiting
         confirmation) so a customer who has paid isn't handed bank details again. --}}
    @if(in_array($invoice->status, ['unpaid', 'overdue']) && ! $awaiting)
        @php
            $bankName    = \App\Models\BillingSetting::get('bank_name');
            $bankAccName = \App\Models\BillingSetting::get('bank_account_name');
            $bankAccNum  = \App\Models\BillingSetting::get('bank_account_number');
            $bankBranch  = \App\Models\BillingSetting::get('bank_branch_code');
        @endphp
        @if($bankName || $bankAccNum)
            <div style="margin-top: 22px;">
                <div class="sec-head">Payment instructions</div>
                <table class="pay num">
                    <tr>
                        <td>@if($bankName)<div class="pay-k">Bank</div><div class="pay-v">{{ $bankName }}</div>@endif</td>
                        <td>@if($bankAccName)<div class="pay-k">Account name</div><div class="pay-v">{{ $bankAccName }}</div>@endif</td>
                    </tr>
                    <tr>
                        <td>@if($bankAccNum)<div class="pay-k">Account number</div><div class="pay-v">{{ $bankAccNum }}</div>@endif</td>
                        <td>@if($bankBranch)<div class="pay-k">Branch code</div><div class="pay-v">{{ $bankBranch }}</div>@endif</td>
                    </tr>
                </table>
                <div class="pay-ref">Please use {{ $invoice->invoice_number }} as your payment reference so we can match your payment quickly.</div>
            </div>
        @endif
    @endif

    {{-- Footer --}}
    <hr class="rule-hair" style="margin: 22px 0 8px;">
    <table class="foot">
        <tr>
            <td>
                <div class="foot-k">Payment terms</div>
                <div class="foot-v">Net {{ $dueDays }}: payment due within {{ $dueDays }} day{{ $dueDays === 1 ? '' : 's' }} of the invoice date.</div>
            </td>
            <td>
                <div class="foot-k">Support</div>
                <div class="foot-v">
                    @if($companyEmail){{ $companyEmail }}<br>@endif
                    @if($companyPhone){{ $companyPhone }}@endif
                </div>
            </td>
            <td class="r">
                <div class="foot-k">Company</div>
                <div class="foot-v">
                    @if($companyReg)Reg {{ $companyReg }}<br>@endif
                    @if($companyVat)VAT {{ $companyVat }}<br>@endif
                    @if($companyWeb){{ $companyWeb }}@endif
                </div>
            </td>
        </tr>
    </table>

    <div class="thanks">Thank you for your business.</div>
    <div class="meta-line">Generated by {{ $companyName }} &nbsp;·&nbsp; {{ now()->format('d M Y') }} &nbsp;·&nbsp; {{ $invoice->invoice_number }}</div>

</body>
</html>
