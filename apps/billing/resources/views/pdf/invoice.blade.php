<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { padding: 48px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 48px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .company-name { font-size: 22px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
        .document-label { font-size: 32px; font-weight: 800; color: #0f172a; letter-spacing: -1px; margin-bottom: 4px; }
        .document-number { font-size: 14px; color: #64748b; }

        /* Meta block */
        .meta { display: table; width: 100%; margin-bottom: 40px; }
        .meta-col { display: table-cell; vertical-align: top; width: 33%; padding-right: 16px; }
        .meta-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; font-weight: 600; margin-bottom: 6px; }
        .meta-value { font-size: 13px; color: #0f172a; font-weight: 500; }
        .meta-secondary { font-size: 12px; color: #64748b; margin-top: 2px; }

        /* Status badge */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-draft     { background: #f1f5f9; color: #475569; }
        .badge-sent      { background: #dbeafe; color: #1e40af; }
        .badge-paid      { background: #dcfce7; color: #15803d; }
        .badge-overdue   { background: #fee2e2; color: #b91c1c; }
        .badge-cancelled { background: #f1f5f9; color: #64748b; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 32px 0; }

        /* Table */
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

        /* Footer */
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e2e8f0; display: table; width: 100%; }
        .footer-left { display: table-cell; vertical-align: top; font-size: 11px; color: #94a3b8; }
        .footer-right { display: table-cell; text-align: right; font-size: 11px; color: #94a3b8; }
        .bank-details { margin-top: 32px; padding: 20px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #0f172a; }
        .bank-title { font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .bank-row { display: table; width: 100%; margin-bottom: 4px; }
        .bank-key { display: table-cell; width: 140px; font-size: 11px; color: #64748b; }
        .bank-val { display: table-cell; font-size: 11px; font-weight: 600; color: #0f172a; }
    </style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $invoice->company->name ?? config('app.name') }}</div>
            @if($invoice->company?->email)
                <div style="color:#64748b;font-size:12px;margin-top:4px;">{{ $invoice->company->email }}</div>
            @endif
            @if($invoice->company?->phone)
                <div style="color:#64748b;font-size:12px;">{{ $invoice->company->phone }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="document-label">INVOICE</div>
            <div class="document-number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top:8px;">
                <span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        </div>
    </div>

    {{-- META --}}
    <div class="meta">
        <div class="meta-col">
            <div class="meta-label">Bill To</div>
            <div class="meta-value">{{ $invoice->client->name ?? '—' }}</div>
            @if($invoice->client?->email)
                <div class="meta-secondary">{{ $invoice->client->email }}</div>
            @endif
            @if($invoice->client?->billing_address)
                <div class="meta-secondary" style="margin-top:4px;white-space:pre-line;">{{ $invoice->client->billing_address }}</div>
            @endif
        </div>
        <div class="meta-col">
            <div class="meta-label">Issue Date</div>
            <div class="meta-value">{{ $invoice->created_at->format('d M Y') }}</div>
            <div style="margin-top:12px;">
                <div class="meta-label">Due Date</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
            </div>
        </div>
        <div class="meta-col" style="text-align:right;padding-right:0;">
            @if($invoice->company?->vat_number)
                <div class="meta-label">VAT Number</div>
                <div class="meta-value">{{ $invoice->company->vat_number }}</div>
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
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">R {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">R {{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right" style="color:#64748b;">Subtotal</td>
                <td class="text-right" style="color:#64748b;">R {{ number_format($invoice->total - $invoice->vat_total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right" style="color:#64748b;">VAT (15%)</td>
                <td class="text-right" style="color:#64748b;">R {{ number_format($invoice->vat_total, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right">Total Due</td>
                <td class="text-right">R {{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- BANK DETAILS --}}
    <div class="bank-details">
        <div class="bank-title">Payment Details</div>
        <div class="bank-row">
            <div class="bank-key">Reference</div>
            <div class="bank-val">{{ $invoice->invoice_number }}</div>
        </div>
        <div class="bank-row" style="margin-top:8px;color:#64748b;font-size:11px;">
            Please use the invoice number as your payment reference.
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-left">
            Thank you for your business.
        </div>
        <div class="footer-right">
            Generated {{ now()->format('d M Y') }}
        </div>
    </div>

</div>
</body>
</html>
