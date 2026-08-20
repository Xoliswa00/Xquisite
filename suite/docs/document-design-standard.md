# Xquisite Document Design & PDF Rendering Standard v1.0

**Status:** Implemented — invoice, receipt, and service agreement contract are live on this standard
**Governs:** every PDF the Suite generates — invoices, quotes, contracts, statements, and everything listed in §3
**Engine:** `barryvdh/laravel-dompdf` (dompdf) rendering Blade views — every rule below is written to be things dompdf can actually do, not aspirational print-CSS
**Shared implementation:** `resources/views/components/document-layout.blade.php` — every document type in §11 extends this component; new document types should too, rather than re-deriving the chrome

This is not a template. It's the grammar a document is built from — so an invoice, a contract, and a credit note all clearly belong to the same company, even though their content is structured completely differently.

---

## 1. Purpose

Today each document type (`billing.invoice-pdf`, `pos.sales.receipt-pdf`, the new `service-delivery.agreements.contract-pdf`) was designed independently. They're close in spirit but not from one system — palette and spacing drift between them, and every new document type re-derives its own rules from scratch.

This standard fixes that at the source: one visual system, one set of layout primitives, one set of pagination rules. New document types are assembled from this, not designed fresh.

**Direction:** institutional / professional / premium. Reference points are accounting firms, commercial banks, legal firms, established consultancies — not SaaS dashboards.

## 2. What this explicitly rules out

These are default instincts from web/dashboard design that must **not** leak into documents:

- Giant headings, oversized document titles
- Rounded "cards" as a structural unit — a document is built from rules and tables, not floating panels
- Pills / rounded-full badges for status
- Large empty whitespace used as a design flourish
- Gradients, decorative blobs, illustration
- More than 2–3 icons per document (an icon-per-line-item look is a dashboard tell)
- Every section wrapped in its own bordered/shadowed container
- Gold (or any accent) used as a background fill or dominant colour
- Centring body content — documents are left-aligned with right-aligned numerics, centring is reserved for the masthead only
- Fake/decorative signature graphics — a signature block is a ruled line and a label, not a cursive-font placeholder
- QR codes unless the document type genuinely needs one (e.g. a POP-linked payment reference) — decorative QR codes are a phone-app tell, not a document convention

## 3. Document types this standard covers

| Document | Status | Primary emphasis |
|---|---|---|
| Invoice | exists (`billing.invoice-pdf`) — needs retrofit | Amount due + how/when to pay |
| Receipt | exists (`pos.sales.receipt-pdf`) — needs retrofit | Proof of payment, what was bought |
| Quote | not yet a PDF (currently email/web-page only via `quotes.show`) | Scope, pricing, validity window |
| Pro-forma Invoice | not built | Expected transaction, not yet a debt |
| Statement | not built | Account activity over a period, running balance |
| Contract | exists (`service-delivery.agreements.contract-pdf`) — needs retrofit | Parties, obligations, signatures |
| Service Agreement / SLA | exists (same file as Contract above) | Scope of service + response times + obligations |
| Purchase Order | not built | What's being ordered, from whom, at what price |
| Credit Note | not built | What's being reversed/adjusted, and why |
| Debit Note | not built | What's being additionally charged, and why |
| Payment Reminder | not built (currently email-only, see `ServiceAgreementPaymentReminderNotification`) | Outstanding amount, days overdue, consequence |
| Proposal | not built | Commercial narrative — the one type allowed more prose |
| Report | not built | Structured analytical content, charts/tables |

Every future document type is a variant of the shared anatomy in §6 — not a new design.

## 4. Colour

Primary is charcoal, not navy, and not gold. Gold is a signal — it marks the one or two things that actually matter on the page — never the document's personality.

| Role | Value | Usage |
|---|---|---|
| Ink (primary text, rules, headings) | `#1A1A1A` | Body text, section rules, table borders |
| Ink secondary | `#4B5563` | Labels, secondary metadata |
| Ink muted | `#9CA3AF` | Footnotes, timestamps, page numbers |
| Accent (gold) | `#C89B3C` | The **one** rule under the masthead, the total-due figure, a status word — nothing else |
| Background | `#FFFFFF` | Always. No tinted document backgrounds. |
| Surface (subtle) | `#F7F7F5` | Reserved for the totals block and table header row only — not general section backgrounds |
| Border | `#D9D9D9` | Table rules, section dividers |
| Status: overdue/negative | `#8B2C2C` | Overdue amounts, debit notes, cancellations |
| Status: settled/positive | `#2F5D3A` | Paid confirmations, credit notes |

Rules:
- No gradients, ever.
- The gold rule under the masthead (see §6) is the *only* full-width use of the accent colour. Everywhere else, gold is text/figure colour on a white background, applied to a single number or word, never a fill.
- Status colours (overdue/settled) are muted, desaturated versions suitable for print — not bright UI red/green.

This retired the navy `#0B2D5B` used in the previous invoice/contract PDFs — all three existing documents are now on this palette (see §11).

## 5. Typography

dompdf renders with its own embedded fonts, not the browser's font stack. Rather than settle for its DejaVu Sans/Serif defaults, **Public Sans** (body/data) and **Libre Caslon Text** (masthead/document title) are embedded directly via local `@font-face` files — both open-source (SIL OFL), both confirmed rendering correctly under dompdf. Font files live at `resources/fonts/documents/*.ttf`, declared once in `document-layout.blade.php`. No other face may be introduced without confirming dompdf support first. This requires `enable_font_subsetting: true` and `enable_php: true` in `config/dompdf.php` (the latter is also what `page_text()` in §9 depends on).

| Level | Face | Size | Weight | Usage |
|---|---|---|---|---|
| Masthead company name | Libre Caslon Text | 13px | 700 | Company name only, top-left |
| Document title | Public Sans | 12–13px | 700, letter-spacing 1.5–2px, uppercase | "TAX INVOICE", "SERVICE AGREEMENT" — restrained, not a headline |
| Section heading | Public Sans | 10.5–11px | 700, uppercase, letter-spacing 0.75–1px | "BILL TO", "PAYMENT TERMS" |
| Body | Public Sans | 9.5–10px | 400 | Paragraph text, table cells |
| Metadata / labels | Public Sans | 8–8.5px | 700, uppercase, letter-spacing 0.5px | Field labels above values |
| Footer / page number | Public Sans | 8px (floor) | 700, uppercase, char-spaced | Reg/VAT, reference, "PAGE X OF Y" — see note below |
| Total figure | Public Sans | 16–18px | 700 | The one number allowed to be large |

**Floor rule**: two tiers only, nothing in between.
- **9.5px+, regular weight** — for anything that's read as continuous prose or data: body text, table cells, contract clauses, addresses, dates. This is the floor for *content*.
- **8–8.5px, but only bold + uppercase + letter-spaced** — for anything that's a genuine label, not content: field labels, metadata, the footer, signature-line captions. Small is only ever allowed when it's also heavy enough to carry its own weight on the page.

Nothing sits between those two tiers, and nothing below 9.5px is ever left at regular weight — small *and* thin at once is what the floor exists to prevent. This was audited across all three implemented documents, not just the footer: the contract's clause body text was found sitting at 9px throughout (below the floor) and bumped to 9.5px; several uppercase field labels in the masthead, signature block, and payment-instructions table were uppercase but still regular weight and got the bold treatment to match.

Rule: **never more than 4 distinct sizes on one page.** If a document needs a 5th size, something in the hierarchy is wrong, not the type scale.

## 6. Document anatomy

Every document is built from the same five zones, top to bottom. A document type can omit a zone (a receipt has no "terms" zone) but never reorders them.

```
┌────────────────────────────────────────────────────────────┐
│  ZONE 1 — MASTHEAD                                          │
│  Company name/logo (left)         Document type (right)     │
│  Address/contact (left)           Reference, date (right)   │
├──────────────────────────────── gold rule, 1.5px ───────────┤
│  ZONE 2 — PARTIES                                            │
│  From (left)                      To / Bill To (right)      │
├─── thin grey rule ────────────────────────────────────────── │
│  ZONE 3 — BODY (document-specific — see §7 grammar)          │
│  Tables, clauses, line items, scope, whatever the type needs │
├─── thin grey rule ────────────────────────────────────────── │
│  ZONE 4 — RESOLUTION                                         │
│  Totals / terms / signature block — whatever the type        │
│  concludes with                                              │
├──────────────────────────────── thin grey rule ─────────────┤
│  ZONE 5 — FOOTER                                              │
│  Company reg/VAT (left)  ·  Support contact (centre)  ·      │
│  Page X of Y (right)                                          │
└────────────────────────────────────────────────────────────┘
```

Margins: 20mm top/bottom, 18mm left/right on A4 portrait. **Set as `margin` on `<body>`, not as `@page { margin: ... }`** — verified empirically in this dompdf install (v3.1.5) that `@page` margin has zero effect on content position; content renders flush to the raw page edges regardless of what the `@page` rule declares, while `size` on `@page` works correctly. This cost real debugging time once already (a masthead/parties layout that measured correctly on paper turned out to be rendering with no margin at all, right up to and past the physical page edge) — don't rediscover it. `@page` is kept in `document-layout.blade.php` for `size: A4` only, with `margin: 0`. Landscape is permitted only for documents whose primary table has 6+ columns (e.g. a detailed statement) — never for contracts.

**Two-column zones must be real `<table>` markup, not flexbox.** dompdf's flexbox support is unreliable enough that a two-column row (masthead, parties, totals, signature block) can render with the right column overflowing past the page edge instead of being constrained to its share of the width — confirmed in this codebase's first retrofit attempt. Every zone in `document-layout.blade.php` that needs a left/right split uses `<table>`/`<td>`, which is dompdf's most exhaustively exercised layout path (also used for every line-item table already).

**A bold `<strong>` immediately following inline text can occasionally overlap the preceding word**, even when the underlying text content is correct (confirmed via `page.get_text()` — the text itself was right, only the paint position was wrong). Root cause not fully isolated (tried moving the space inside the bold span, `margin-left` up to 5px, and `display:inline-block` — none reliably fixed it); it appears to be a rare dompdf inline-run positioning bug tied to a specific font-weight transition, not a general rule violation. If you hit this: don't spend long chasing it with CSS. The reliable fix is to not force a font-weight change mid-sentence around the affected phrase — drop the `<strong>` on that one instance rather than the whole layout technique.

Zone 1 and Zone 5 repeat identically on every page of a multi-page document (see §9).

## 7. Document grammar — hierarchy is the type, not the theme

The same five zones carry different *emphasis* per document. This is the part that stops every document from looking like a reskinned invoice.

**Invoice** — the reader must get three facts in one glance: who issued it, what they owe, when it's due.
`Masthead → Bill To → Line items → Totals (Total Due gets the accent colour + largest size) → Payment instructions → Footer`

**Quote** — the reader is deciding whether to say yes.
`Masthead → Client + Project → Scope/Deliverables → Pricing → Assumptions/Exclusions → Validity date → Acceptance block → Footer`

**Contract / Service Agreement** — the reader needs to find a specific clause fast, six months from now.
`Masthead → Parties → Effective Date → Definitions → Scope → Obligations → Fees → Term → Termination → Liability → Confidentiality → Dispute Resolution → Signatures → Footer`
Numbered clauses throughout (already the convention in the current contract PDF — keep it). This is the one document type where dense, small, numbered prose is correct — resist the urge to "open it up" visually.

**Receipt** — proof, not persuasion. Shortest document in the system.
`Masthead → Paid-by / Date → Line items → Amount paid + method → Footer`

**Statement** — a ledger, not a story.
`Masthead → Account holder → Opening balance → Transaction table (date-ordered) → Closing balance → Footer`
Landscape-eligible if the transaction table needs it.

**Credit Note / Debit Note** — must never be mistakable for an invoice at a glance.
Same skeleton as an invoice, but the document title band uses the status colour (`#2F5D3A` for credit, `#8B2C2C` for debit) as a **1px top rule only**, not a fill, and the total is prefixed with the sign (`−R450.00` / `+R450.00`).

**Payment Reminder** — urgency without alarm.
`Masthead → Account → Outstanding amount (large, status colour) → Days overdue → Consequence (plain sentence, not a warning box) → Payment instructions → Footer`
No red banners, no exclamation iconography — the number itself carries the weight.

**Proposal** — the one type allowed narrative prose and a cover section.
`Masthead/cover → Executive summary → Understanding of the need → Approach → Pricing → Timeline → Why us (short) → Acceptance → Footer`

**Report** — content-led, tables and charts are the point.
`Masthead → Period covered → Summary metrics → Sections (table/chart per finding) → Footer`
Charts follow the Suite's existing `dataviz` skill palette rules, not this document's gold/charcoal palette, when a chart is embedded — the surrounding document chrome (masthead, footer, headings) still follows this standard.

## 8. Tables

- No rounded containers around tables. A table is bounded by rules, not a card.
- Header row: `#F7F7F5` fill, 700 weight, uppercase, 1px bottom border in ink colour — not the accent colour.
- Body rows: no fill; 0.5–0.75px bottom border in `#D9D9D9` between rows; no zebra striping (it reads as a spreadsheet export, not a document).
- Alignment: descriptions left, quantities/dates centre-or-right (pick one per document and stay consistent), currency **always** right-aligned with consistent decimal position.
- Totals row: top border 1.5px in ink (not accent), figure itself may use the accent colour and larger size — the row background stays white, only the figure carries emphasis.
- Column widths are fixed proportions per document type (already the convention in `billing.invoice-pdf`'s `.col-desc`/`.col-qty` etc.) — never auto-width, which causes inconsistent alignment across a run of documents.

## 9. Pagination & multi-page rules

These are the rules that stop a generated document from looking machine-assembled when it spans pages. dompdf-specific, not generic advice:

1. **Repeat table headers automatically** — use a real `<thead>` for every data table; dompdf repeats `<thead>` content on every page a `<table>` spans. Never simulate a header with a styled first `<tr>` inside `<tbody>`.
2. **Never split a row.** Wrap each table in `page-break-inside: avoid` is not reliable *within* a single `<tr>` in dompdf — the real guarantee is a `<thead>`-driven table, which dompdf paginates row-by-row without splitting a row's cells across pages by default. Verify this holds for any table with tall cells (wrapped descriptions).
3. **Keep totals together.** Wrap the totals block (Zone 4 numeric summary) in a container with `page-break-inside: avoid`. If it doesn't fit on the current page, the whole block moves to the next page rather than splitting subtotal from total.
4. **Keep signature blocks together.** Same rule — `page-break-inside: avoid` on the signature block container. A lone signature line stranded at the top of a new page with no names/labels is the single most obvious "this was auto-generated" tell.
5. **Never orphan a section heading.** A heading must not be the last line on a page with its content starting on the next. In practice: keep heading + its first paragraph/table-header in one `page-break-inside: avoid` wrapper.
6. **Footers never overlap content.** Reserve the bottom margin (20mm) as dead space — content must not be allowed to flow into it. Use dompdf's `page_text()` callback (see below) for the footer rather than an in-flow footer element, so it's guaranteed to sit in the margin on every page regardless of content length.
7. **Page numbers: "PAGE X OF Y".** Implemented in `document-layout.blade.php` via dompdf's PHP-in-view hook. `page_text()` draws raw canvas text, not HTML, so it doesn't inherit any CSS — bold weight has to be requested explicitly via `$fontMetrics->getFont()` (both `$pdf` and `$fontMetrics` are bound automatically inside the script block by dompdf's `PhpEvaluator`), and letter-spacing via the `$char_space` parameter, not CSS:
   ```php
   <script type="text/php">
   if (isset($pdf)) {
       $footerFont = $fontMetrics->getFont('Public Sans', 'bold');
       $pdf->page_text(470, 805, "REFERENCE · PAGE {PAGE_NUM} OF {PAGE_COUNT}", $footerFont, 8, [0.29, 0.33, 0.39], 0.0, 0.3);
   }
   </script>
   ```
   Coordinates are in points on A4 (595×842pt) — adjust `x`/`y` to sit inside the footer zone, right-aligned. This is also why the footer floor is 8px/bold rather than 7.5px/regular (§5) — `page_text()` has no size floor of its own to enforce, so it's easy to leave it at whatever default reads thin on the page.
8. **Continuation labels on page 2+** of a long document (contract, statement, report): repeat the document reference in a small top-right label — `"Invoice INV-2026-0042 — Continued"` — via the same `page_text()` mechanism, conditioned on page number > 1 where dompdf's callback supports it, or as a fixed-position element if simpler.
9. **Never shrink body text to force one-page fit.** Minimum body size is 9.5px per §5. A document that needs 3 pages takes 3 pages — the alternative (7px body text) is what makes a document look cheap.
10. **Long descriptions wrap and continue naturally** — do not truncate. A wrapped description that pushes a table across a page boundary is expected and handled by rule 1–2, not avoided by truncating text.
11. **Explicit page breaks for contracts**: force a break before the Signatures section (`page-break-before: always` on that container) whenever the preceding clause content leaves less than ~120pt of remaining page height — prevents a signature block sitting awkwardly 2 lines below a clause on an otherwise near-empty page. (This threshold needs a real value once we're retrofitting the contract template — flagging as a concrete follow-up in §11, not a rule to take as exact yet.)
12. **Vertical rhythm**: consistent 10–14px gap between zones regardless of document type, so a one-page receipt and a five-page contract still feel like they came from the same system.

## 10. Metadata every document must carry

Regardless of type, Zone 1 always includes:
- Document type label (uppercase, restrained — not a headline)
- A unique reference (existing convention: `INV-2026-0042` style prefix per type — extend this scheme to new types, e.g. `QT-`, `CN-` credit note, `DN-` debit note, `PO-` purchase order, `STMT-`)
- Issue date
- A second relevant date where applicable (due date / valid-until / period covered)

Zone 5 always includes:
- Company registration + VAT number (from `BillingSetting`, already the convention)
- One support contact line
- Page X of Y

## 11. Migration — status

All three pre-existing PDFs are retrofitted onto this standard, via the shared `<x-document-layout>` component:

| File | Status |
|---|---|
| `resources/views/billing/invoice-pdf.blade.php` | Done — charcoal/gold palette, table rules §8, `page_text()` page numbers |
| `resources/views/pos/sales/receipt-pdf.blade.php` | Done — same component, shortest grammar (§7) |
| `resources/views/service-delivery/agreements/contract-pdf.blade.php` | Done — signature block wrapped in `.keep-together`; this is the document most likely to span multiple pages, so it's the one to watch in real use |

Any new document type from §3 should extend `<x-document-layout>` rather than rebuild the masthead/footer/font chrome — that's the entire point of pulling it into one component.

**Not yet done, worth a follow-up pass**: real multi-page testing against `.keep-together` and the footer's fixed x-coordinates (§9) with actual long content (a genuinely multi-page contract), rather than the single-page test documents used to verify the retrofit renders cleanly.

## 12. Decisions made during implementation

1. **Charcoal-primary supersedes the previous navy convention** — implemented as such. The dashboard UI's own tokens are unaffected; this only ever governed generated PDFs.
2. **Typefaces**: Public Sans + Libre Caslon Text, embedded locally (§5) — resolved in favour of real embedded fonts over dompdf's DejaVu defaults, since dompdf supports local `@font-face` without any external service.
3. **Reference number schemes** for the not-yet-built types (`QT-`, `CN-`, `DN-`, `PO-`, `STMT-`) — still open, needed before `Quote::booted()`-style prefix generation gets replicated for each new type.
4. **Whether Proposal and Report** should inherit a looser version of §8's table rules — still open, flagged in §7, not yet relevant since neither exists as a PDF yet.
