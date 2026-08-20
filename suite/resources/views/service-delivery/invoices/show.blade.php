<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-[#D4AF37]">{{ $invoice->invoice_number }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    @if($invoice->status === 'paid') bg-emerald-900/40 text-emerald-400
                    @elseif($invoice->isOverdue()) bg-red-900/40 text-red-400
                    @elseif($invoice->status === 'sent') bg-yellow-900/40 text-yellow-400
                    @elseif($invoice->status === 'cancelled') bg-panel text-ink-faint
                    @else bg-panel-2 text-ink-muted border border-line-2 @endif">
                    {{ $invoice->isOverdue() ? 'Overdue' : ucfirst($invoice->status) }}
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('invoices.index') }}" class="text-sm text-ink-muted hover:text-ink self-center">&larr; Invoices</a>
                <a href="{{ route('invoices.download', $invoice) }}" class="px-3 py-2 bg-panel-2 hover:bg-line-2 text-ink text-sm rounded-lg">Download PDF</a>
                @if($invoice->status !== 'paid')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Client</p>
                <p class="text-ink font-medium">{{ $invoice->client?->name ?? '—' }}</p>
                <p class="text-ink-faint text-xs mt-0.5">{{ $invoice->client?->email }}</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Issue / Due</p>
                <p class="text-ink font-medium">{{ $invoice->issue_date->format('d M Y') }}</p>
                <p class="text-ink-faint text-xs mt-0.5">Due {{ $invoice->due_date->format('d M Y') }} ({{ $invoice->payment_terms }})</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Total</p>
                <p class="text-[#0078D4] font-semibold">R{{ number_format($invoice->total, 2) }}</p>
                <p class="text-ink-faint text-xs mt-0.5">VAT {{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}% included</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Balance Due</p>
                <p class="{{ $invoice->balanceDue() > 0 ? 'text-[#C89B3C]' : 'text-emerald-400' }} font-semibold">R{{ number_format($invoice->balanceDue(), 2) }}</p>
                <p class="text-ink-faint text-xs mt-0.5">R{{ number_format($invoice->amount_paid, 2) }} paid</p>
            </div>
        </div>

        <div class="bg-panel-2 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-line-2">
                <h3 class="text-sm font-semibold text-ink-muted">Line Items</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line-2 text-ink-muted text-left">
                        <th class="px-4 py-2 font-medium">Description</th>
                        <th class="px-4 py-2 font-medium text-right">Qty</th>
                        <th class="px-4 py-2 font-medium text-right">Unit Price</th>
                        <th class="px-4 py-2 font-medium text-right">Discount</th>
                        <th class="px-4 py-2 font-medium text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-2">
                    @foreach($invoice->line_items as $item)
                        @php
                            $qty = (float) $item['quantity'];
                            $unitPrice = (float) $item['unit_price'];
                            $discountPct = (float) ($item['discount_percent'] ?? 0);
                            $lineTotal = round($qty * $unitPrice * (1 - $discountPct / 100), 2);
                        @endphp
                        <tr>
                            <td class="px-4 py-2.5 text-ink">{{ $item['description'] }}</td>
                            <td class="px-4 py-2.5 text-ink-muted text-right">{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td>
                            <td class="px-4 py-2.5 text-ink-muted text-right">R{{ number_format($unitPrice, 2) }}</td>
                            <td class="px-4 py-2.5 text-ink-muted text-right">{{ $discountPct > 0 ? $discountPct . '%' : '—' }}</td>
                            <td class="px-4 py-2.5 text-ink text-right">R{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!in_array($invoice->status, ['paid', 'cancelled']))
            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">Actions</h3>
                <div class="flex flex-wrap gap-3">
                    @if($invoice->status === 'draft')
                        <form method="POST" action="{{ route('invoices.send', $invoice) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">Mark as Sent</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Cancel this invoice?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm rounded-lg border border-red-800">Cancel Invoice</button>
                    </form>
                </div>

                <div class="pt-4 border-t border-line-2">
                    <h4 class="text-xs font-semibold text-ink-faint uppercase mb-3">Record Payment</h4>
                    <form method="POST" action="{{ route('invoices.record-payment', $invoice) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-ink-faint mb-1">Amount</label>
                            <input type="number" name="amount_paid" step="0.01" min="0.01" value="{{ $invoice->balanceDue() }}" required
                                   class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2 w-32">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink-faint mb-1">Date</label>
                            <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required
                                   class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink-faint mb-1">Method</label>
                            <select name="payment_method" required class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                                <option value="eft">EFT</option>
                                <option value="card">Card</option>
                                <option value="cash">Cash</option>
                                <option value="debit_order">Debit Order</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-ink-faint mb-1">Reference</label>
                            <input type="text" name="payment_reference" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-emerald-900/40 hover:bg-emerald-800/50 text-emerald-400 text-sm rounded-lg border border-emerald-800">Record</button>
                    </form>
                </div>
            </div>
        @elseif($invoice->status === 'paid')
            <div class="bg-emerald-900/20 border border-emerald-800 rounded-xl p-5 text-sm text-emerald-300">
                Paid in full on {{ $invoice->paid_at?->format('d F Y') }}
                @if($invoice->payment_method) via {{ strtoupper($invoice->payment_method) }}@endif
                @if($invoice->payment_reference) · Ref: {{ $invoice->payment_reference }}@endif
            </div>
        @endif

        @if($invoice->notes)
            <div class="bg-panel-2 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-ink-muted mb-2">Notes</h3>
                <p class="text-sm text-ink-muted">{{ $invoice->notes }}</p>
            </div>
        @endif

    </div>
</x-app-layout>
