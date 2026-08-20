@props([
    'docType',           // e.g. "Tax Invoice", "Receipt", "Service Agreement"
    'reference',         // e.g. "INV-2026-0042"
    'dateLabel' => 'Issue Date',
    'date' => null,
    'secondDateLabel' => null,   // e.g. "Due Date", "Valid Until"
    'secondDate' => null,
    'statusLabel' => null,       // e.g. "Paid", "Overdue"
    'statusVariant' => null,     // 'positive' | 'negative' | null (neutral)
])

@php
    $companyName  = \App\Models\BillingSetting::get('company_name') ?: config('app.name');
    $companyAddr  = \App\Models\BillingSetting::get('company_address');
    $companyReg   = \App\Models\BillingSetting::get('company_registration');
    $companyVat   = \App\Models\BillingSetting::get('company_vat');
    $companyEmail = \App\Models\BillingSetting::get('company_email');
    $companyPhone = \App\Models\BillingSetting::get('company_phone');

    // Logo is optional — company_logo_path is a path relative to storage/app/public
    // (same convention as Tenant::logo_url's upload target). Resolved to an
    // absolute local path since dompdf needs that for reliable image embedding
    // (remote/URL images require enable_remote, which stays off). Falls back to
    // text-only masthead if no logo is configured or the file is missing.
    $logoRelativePath = \App\Models\BillingSetting::get('company_logo_path');
    $logoAbsolutePath = $logoRelativePath ? storage_path('app/public/' . ltrim($logoRelativePath, '/')) : null;
    $hasLogo = $logoAbsolutePath && file_exists($logoAbsolutePath);

    // Footer labels are uppercased to match the "metadata" type treatment
    // (§5 of the design standard) — the email/phone stays natural-case since
    // an uppercased email address reads as broken, not as a label.
    $footerLeft = trim(collect([
        $companyReg ? "REG {$companyReg}" : null,
        $companyVat ? "VAT {$companyVat}" : null,
    ])->filter()->implode('  ·  '));

    $footerCentre = $companyEmail ?: $companyPhone;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    /*
     * Xquisite Document Design Standard v1.0 — shared chrome.
     * See suite/docs/document-design-standard.md. Every generated PDF
     * (invoice, receipt, service agreement, and future types) extends this.
     *
     * CSS is intentionally hand-rolled with literal hex values, not custom
     * properties (var(--x)) — dompdf's support for CSS custom properties is
     * unreliable, so this file is the single source of truth for the palette
     * instead of a token layer.
     */

    @font-face {
        font-family: 'Public Sans';
        src: url('{{ resource_path('fonts/documents/PublicSans-Regular.ttf') }}') format('truetype');
        font-weight: 400; font-style: normal;
    }
    @font-face {
        font-family: 'Public Sans';
        src: url('{{ resource_path('fonts/documents/PublicSans-Medium.ttf') }}') format('truetype');
        font-weight: 500; font-style: normal;
    }
    @font-face {
        font-family: 'Public Sans';
        src: url('{{ resource_path('fonts/documents/PublicSans-SemiBold.ttf') }}') format('truetype');
        font-weight: 600; font-style: normal;
    }
    @font-face {
        font-family: 'Public Sans';
        src: url('{{ resource_path('fonts/documents/PublicSans-Bold.ttf') }}') format('truetype');
        font-weight: 700; font-style: normal;
    }
    @font-face {
        font-family: 'Libre Caslon Text';
        src: url('{{ resource_path('fonts/documents/LibreCaslonText-Regular.ttf') }}') format('truetype');
        font-weight: 400; font-style: normal;
    }
    @font-face {
        font-family: 'Libre Caslon Text';
        src: url('{{ resource_path('fonts/documents/LibreCaslonText-Bold.ttf') }}') format('truetype');
        font-weight: 700; font-style: normal;
    }
    @font-face {
        font-family: 'Libre Caslon Text';
        src: url('{{ resource_path('fonts/documents/LibreCaslonText-Italic.ttf') }}') format('truetype');
        font-weight: 400; font-style: italic;
    }

    /* @page { margin: ... } has no effect on content position in this
       dompdf version — verified empirically, content rendered flush to the
       raw page edges (x=0 to page width) regardless of the @page rule.
       Margin has to be set on <body> instead; @page is kept for paper size
       only. See design-standard.md §6 if this changes in a future dompdf
       version. */
    @page {
        size: A4;
        margin: 0;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Public Sans', sans-serif;
        font-size: 9.5px;
        line-height: 1.5;
        color: #1A1A1A;
        background: #FFFFFF;
        margin: 20mm 18mm;
    }

    .serif { font-family: 'Libre Caslon Text', serif; }
    .num   { font-variant-numeric: tabular-nums; }

    /* ── Zone 1 — Masthead ──────────────────────────────────────── */
    /* Real <table> layout, not flexbox — dompdf's flexbox support is
       unreliable enough that long right-column content (reference numbers,
       dates) can overflow past the page edge instead of being constrained
       to its column. Tables are dompdf's most exhaustively tested layout
       path in this codebase, so every two-column zone below uses one. */
    table.masthead { width: 100%; border-collapse: collapse; }
    table.masthead td { vertical-align: top; padding: 0; }
    table.masthead td.doc-block { text-align: right; }
    .masthead .logo { height: 30px; margin-bottom: 6px; }
    .masthead .company-name { font-family: 'Libre Caslon Text', serif; font-weight: 700; font-size: 13px; color: #1A1A1A; }
    .masthead .company-addr { font-size: 9.5px; font-weight: 500; color: #4B5563; margin-top: 2px; }
    .masthead .doc-type {
        font-family: 'Public Sans', sans-serif; font-weight: 700; font-size: 12px;
        letter-spacing: 1.5px; text-transform: uppercase; color: #1A1A1A;
    }
    .masthead .doc-meta { margin-top: 5px; }
    .masthead .doc-meta .row { font-size: 9.5px; font-weight: 500; color: #4B5563; }
    .masthead .doc-meta .row b { color: #1A1A1A; font-weight: 700; }
    .masthead .status {
        display: inline-block; margin-top: 5px; font-size: 8.5px; font-weight: 700;
        letter-spacing: 0.5px; text-transform: uppercase;
    }
    .masthead .status.positive { color: #2F5D3A; }
    .masthead .status.negative { color: #8B2C2C; }

    .gold-rule { height: 1.5px; background: #C89B3C; margin: 10px 0 14px; }
    .rule { border: none; border-top: 0.75px solid #D9D9D9; margin: 12px 0; }

    /* ── Zone 2 — Parties ───────────────────────────────────────── */
    table.parties { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.parties td { vertical-align: top; padding: 0; width: 50%; }
    table.parties td.party-right { text-align: right; padding-left: 32px; }
    .parties .label {
        font-size: 8px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
        color: #9CA3AF; margin-bottom: 3px;
    }
    .parties .name { font-size: 10.5px; font-weight: 600; color: #1A1A1A; }
    .parties .line { font-size: 9.5px; color: #4B5563; line-height: 1.4; }

    /* ── Section headings ───────────────────────────────────────── */
    h3.section-heading {
        font-size: 10px; font-weight: 700; letter-spacing: 0.75px; text-transform: uppercase;
        color: #1A1A1A; margin-bottom: 8px;
    }

    /* ── Tables ─────────────────────────────────────────────────── */
    table.doc-table { width: 100%; border-collapse: collapse; }
    table.doc-table thead th {
        text-align: left; font-size: 8.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: #1A1A1A; background: #F7F7F5;
        border-bottom: 1px solid #1A1A1A; padding: 6px 8px;
    }
    table.doc-table thead th.num-col { text-align: right; }
    table.doc-table tbody td { padding: 6px 8px; font-size: 9.5px; color: #1A1A1A; border-bottom: 0.5px solid #D9D9D9; }
    table.doc-table tbody td.num-col { text-align: right; }
    table.doc-table tbody tr:last-child td { border-bottom: none; }

    /* ── Zone 4 — Resolution (totals / terms / signatures) ─────── */
    .totals-wrap { width: 100%; }
    table.totals { width: 230px; margin-left: auto; border-collapse: collapse; }
    table.totals td { padding: 2.5px 0; font-size: 9.5px; color: #4B5563; }
    table.totals td.num-col { text-align: right; }
    table.totals tr.t-rule td { border-top: 1.5px solid #1A1A1A; padding-top: 6px; }
    table.totals tr.t-final td { font-weight: 700; }
    table.totals tr.t-final td.k { font-size: 10.5px; letter-spacing: 0.5px; text-transform: uppercase; color: #1A1A1A; }
    table.totals tr.t-final td.v { font-size: 17px; color: #C89B3C; }

    table.sig-block { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table.sig-block td { vertical-align: top; width: 50%; }
    table.sig-block td.sig-right { padding-left: 40px; }
    .sig-line { border-bottom: 0.75px solid #1A1A1A; height: 24px; margin-bottom: 3px; }
    .sig-label { font-size: 8px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: #9CA3AF; }

    /* Keep-together wrappers — see design-standard.md §9.3–9.4 */
    .keep-together { page-break-inside: avoid; }
</style>
</head>
<body>

<table class="masthead">
    <tr>
        <td>
            @if($hasLogo)
                <img src="{{ $logoAbsolutePath }}" class="logo" alt="{{ $companyName }}">
            @endif
            <div class="company-name">{{ $companyName }}</div>
            @if($companyAddr)<div class="company-addr">{{ $companyAddr }}</div>@endif
            <div class="company-addr">
                {{ collect([$companyEmail, $companyPhone])->filter()->implode('  ·  ') }}
            </div>
        </td>
        <td class="doc-block">
            <div class="doc-type serif">{{ $docType }}</div>
            <div class="doc-meta num">
                <div class="row"><b>{{ $reference }}</b></div>
                <div class="row">{{ $dateLabel }}: {{ $date }}</div>
                @if($secondDateLabel)
                    <div class="row">{{ $secondDateLabel }}: {{ $secondDate }}</div>
                @endif
            </div>
            @if($statusLabel)
                <div class="status {{ $statusVariant }}">{{ $statusLabel }}</div>
            @endif
        </td>
    </tr>
</table>

<div class="gold-rule"></div>

@isset($parties)
    <table class="parties"><tr>{{ $parties }}</tr></table>
    <hr class="rule">
@endisset

{{ $slot }}

@isset($resolution)
    <hr class="rule">
    <div class="keep-together">{{ $resolution }}</div>
@endisset

{{-- Zone 5 — footer + page numbers. Rendered via dompdf's page_text() rather
     than flowing HTML, so it repeats correctly in the margin on every page
     (see design-standard.md §9.6–9.7). Requires config/dompdf.php
     'enable_php' => true. --}}
<script type="text/php">
if (isset($pdf)) {
    // Bold + uppercase + letter-spacing, matching the "metadata" type
    // treatment (§5) — 7.5px regular gray was too thin to hold up as a
    // standalone line on the page; small text stays legible by being
    // heavier and spaced out, not just larger.
    $footerFont = $fontMetrics->getFont('Public Sans', 'bold');
    $footerColor = array(0.29, 0.33, 0.39); // #4B5563
    $footerSize = 8;
    $footerCharSpace = 0.3;

    $pdf->page_text(56, 805, "{{ addslashes($footerLeft) }}", $footerFont, $footerSize, $footerColor, 0.0, $footerCharSpace);
    $pdf->page_text(245, 805, "{{ addslashes($footerCentre) }}", $footerFont, $footerSize, $footerColor, 0.0, $footerCharSpace);
    $pdf->page_text(470, 805, "{{ addslashes(strtoupper($reference)) }} · PAGE {PAGE_NUM} OF {PAGE_COUNT}", $footerFont, $footerSize, $footerColor, 0.0, $footerCharSpace);
}
</script>

</body>
</html>
