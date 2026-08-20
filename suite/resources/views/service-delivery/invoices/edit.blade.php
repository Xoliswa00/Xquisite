<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.show', $invoice) }}" class="text-ink-faint hover:text-ink-muted">&larr; {{ $invoice->invoice_number }}</a>
            <span class="text-ink-faint">/</span>
            <h2 class="text-xl font-semibold text-ink">Edit Invoice</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{
            items: @js(collect($invoice->line_items)->map(fn($i) => [
                'description' => $i['description'], 'quantity' => (float) $i['quantity'],
                'unit_price' => (float) $i['unit_price'], 'discount_percent' => (float) ($i['discount_percent'] ?? 0),
                'total' => round((float) $i['quantity'] * (float) $i['unit_price'] * (1 - (float) ($i['discount_percent'] ?? 0) / 100), 2),
            ])),
            taxRate: {{ old('tax_rate', $invoice->tax_rate) }},
            addItem() { this.items.push({ description: '', quantity: 1, unit_price: 0, discount_percent: 0, total: 0 }); },
            removeItem(i) { this.items.splice(i, 1); },
            updateTotal(i) {
                const it = this.items[i];
                const gross = (+it.quantity || 0) * (+it.unit_price || 0);
                it.total = +(gross * (1 - (+it.discount_percent || 0) / 100)).toFixed(2);
            },
            subtotal() { return this.items.reduce((s, r) => s + (+r.total || 0), 0); },
            tax()      { return +(this.subtotal() * this.taxRate / 100).toFixed(2); },
            total()    { return +(this.subtotal() + this.tax()).toFixed(2); },
         }">

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm space-y-1">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <p class="text-xs text-ink-faint">Client: <span class="text-ink font-medium">{{ $invoice->client?->name }}</span> — can't be changed after creation.</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Payment Terms *</label>
                        <input type="text" name="payment_terms" value="{{ old('payment_terms', $invoice->payment_terms) }}" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Issue Date *</label>
                        <input type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date->toDateString()) }}" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Due Date *</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date->toDateString()) }}" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="bg-panel-2 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-line-2 flex items-center justify-between">
                    <h3 class="font-semibold text-[#D4AF37]">Line Items</h3>
                    <button type="button" @click="addItem()" class="text-xs text-[#0078D4] hover:text-[#002B5B] font-medium">+ Add line</button>
                </div>

                <div class="p-5 space-y-3">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="item.description" :name="'line_items['+i+'][description]'" placeholder="Description" required
                                   class="flex-1 bg-app border-line-2 text-ink rounded-lg px-3 py-2 text-sm">
                            <input type="number" x-model.number="item.quantity" :name="'line_items['+i+'][quantity]'" min="0" step="0.5"
                                   @input="updateTotal(i)" placeholder="Qty"
                                   class="w-16 bg-app border-line-2 text-ink rounded-lg px-2 py-2 text-sm">
                            <div class="relative w-24">
                                <span class="absolute inset-y-0 left-2 flex items-center text-ink-faint text-xs">R</span>
                                <input type="number" x-model.number="item.unit_price" :name="'line_items['+i+'][unit_price]'" min="0" step="0.01"
                                       @input="updateTotal(i)" placeholder="0.00"
                                       class="w-full bg-app border-line-2 text-ink rounded-lg pl-5 pr-2 py-2 text-sm">
                            </div>
                            <div class="relative w-16">
                                <input type="number" x-model.number="item.discount_percent" :name="'line_items['+i+'][discount_percent]'" min="0" max="100" step="1"
                                       @input="updateTotal(i)" placeholder="0"
                                       class="w-full bg-app border-line-2 text-ink rounded-lg pl-2 pr-4 py-2 text-sm">
                                <span class="absolute inset-y-0 right-1.5 flex items-center text-ink-faint text-xs">%</span>
                            </div>
                            <p class="w-24 text-sm font-medium text-ink text-right" x-text="'R ' + item.total.toFixed(2)"></p>
                            <button type="button" @click="removeItem(i)" class="text-ink-faint hover:text-red-400" x-show="items.length > 1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                    <p class="text-xs text-ink-faint">Qty / Unit Price / Discount %</p>
                </div>

                <div class="px-5 pb-5 space-y-2 border-t border-line-2 pt-4">
                    <div class="flex justify-between text-sm text-ink-muted">
                        <span>Subtotal</span><span x-text="'R ' + subtotal().toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-ink-muted">
                        <span class="flex items-center gap-2">
                            VAT
                            <input type="number" name="tax_rate" min="0" max="100" step="0.5" x-model.number="taxRate"
                                   class="w-16 bg-panel border-line-2 rounded px-2 py-0.5 text-xs">
                            <span class="text-xs">%</span>
                        </span>
                        <span x-text="'R ' + tax().toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-ink text-base border-t border-line-2 pt-2">
                        <span>Total</span><span x-text="'R ' + total().toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <div class="bg-panel-2 rounded-xl p-5">
                <label class="block text-sm font-medium text-ink-muted mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full bg-app border-line-2 text-ink rounded-lg px-3 py-2 text-sm resize-none">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 text-sm text-ink-faint hover:text-ink">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
