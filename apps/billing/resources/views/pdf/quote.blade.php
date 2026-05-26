<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quote {{ $quote->quote_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { padding: 48px; }

        .header { display: table; width: 100%; margin-bottom: 48px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .company-name { font-size: 22px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
        .document-label { font-size: 32px; font-weight: 800; color: #0f172a; letter-spacing: -1px; margin-bottom: 4px; }
        .document-number { font-size: 14px; color: #64748b; }

        .meta { display: table; width: 100%; margin-bottom: 40px; }
        .meta-col { display: table-cell; vertical-align: top; width: 33%; padding-right: 16px; }
        .meta-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; font-weight: 600; margin-bottom: 6px; }
        .meta-value { font-size: 13px; color: #0f172a; font-weight: 500; }
        .meta-secondary { font-size: 12px; color: #64748b; margin-top: 2px; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-draft    { background: #f1f5f9; color: #475569; }
        .badge-sent     { background: #dbeafe; color: #1e40af; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-rejected { background: #fee2e2; color: #b91c1c; }
        .badge-invoiced { background: #f3e8ff; color: #7c3aed; }

        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 32px 0; }

        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead tr { background: #0f172a; }
        .items-table thead th { padding: 10px 14px; color: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600; text-align: left; }
        .items-table thead th.text-right { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .items-table tbody tr:last-child { border-bottom: none; }
        .items-table tbody td { padding: 12px 14px; font-size: 13px; color: #334155; vertical-align: top; }
        .items-table tbody td.text-right { text-align: right; }
        .items-table tfoot tr { background: #f8fafc; }
        .items-table tfoot td { padding: 10px 14px; font-size: 13px; }
        .items-table tfoot td.text-right { text-align: right; }
        .items-table tfoot tr.total-row td { font-size: 15px; font-weight: 700; color: #0f172a; border-top: 2px solid #0f172a; }

        .validity { margin-top: 32px; padding: 16px 20px; background: #fffbeb; border-radius: 8px; border-left: 3px solid #f59e0b; font-size: 12px; color: #92400e; }

        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e2e8f0; display: table; width: 100%; }
        .footer-left { display: table-cell; vertical-align: top; font-size: 11px; color: #94a3b8; }
        .footer-right { display: table-cell; text-align: right; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $quote->company->name ?? config('app.name') }}</div>
            @if($quote->company?->email)
                <div style="color:#64748b;font-size:12px;margin-top:4px;">{{ $quote->company->email }}</div>
            @endif
            @if($quote->company?->phone)
                <div style="color:#64748b;font-size:12px;">{{ $quote->company->phone }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="document-label">QUOTE</div>
            <div class="document-number">{{ $quote->quote_number }}</div>
            <div style="margin-top:8px;">
                <span class="badge badge-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
            </div>
        </div>
    </div>

    {{-- META --}}
    <div class="meta">
        <div class="meta-col">
            <div class="meta-label">Prepared For</div>
            <div class="meta-value">{{ $quote->client->name ?? '—' }}</div>
            @if($quote->client?->email)
                <div class="meta-secondary">{{ $quote->client->email }}</div>
            @endif
            @if($quote->client?->billing_address)
                <div class="meta-secondary" style="margin-top:4px;white-space:pre-line;">{{ $quote->client->billing_address }}</div>
            @endif
        </div>
        <div class="meta-col">
            <div class="meta-label">Issue Date</div>
            <div class="meta-value">{{ $quote->created_at->format('d M Y') }}</div>
        </div>
        <div class="meta-col" style="text-align:right;padding-right:0;">
            @if($quote->company?->vat_number)
                <div class="meta-label">VAT Number</div>
                <div class="meta-value">{{ $quote->company->vat_number }}</div>
            @endif
        </div>
    </div>

    <hr class="divider">

    {{-- LINE ITEMS --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%;">Description</th>
                <th style="width:12%;text-align:right;">Qty</th>
                <th style="width:19%;text-align:right;">Unit Price</th>
                <th style="width:19%;text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">
                        @if($item->unit_price == 0)
                            <span style="color:#15803d;font-size:11px;">Included</span>
                        @else
                            R {{ number_format($item->unit_price, 2) }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($item->total == 0)
                            <span style="color:#15803d;font-size:11px;">—</span>
                        @else
                            R {{ number_format($item->total, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right" style="color:#64748b;">Subtotal</td>
                <td class="text-right" style="color:#64748b;">R {{ number_format($quote->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right" style="color:#64748b;">VAT (15%)</td>
                <td class="text-right" style="color:#64748b;">R {{ number_format($quote->vat, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">R {{ number_format($quote->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- VALIDITY NOTE --}}
    <div class="validity">
        This quote is valid for 30 days from the issue date. Prices are subject to change after the validity period.
        To accept this quote, please contact us or respond in writing.
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-left">
            {{ $quote->company->name ?? config('app.name') }} — Thank you for your interest.
        </div>
        <div class="footer-right">
            Generated {{ now()->format('d M Y') }}
        </div>
    </div>

</div>
</body>
</html>
