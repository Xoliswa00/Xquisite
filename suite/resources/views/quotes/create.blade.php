<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('quotes.index') }}" class="text-gray-400 hover:text-gray-600">← Quotes</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold">New Quote</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{
            items: [{ name: '', qty: 1, unit: 'item', unit_price: 0, total: 0 }],
            taxRate: 0,
            depositPct: 50,
            addItem() { this.items.push({ name: '', qty: 1, unit: 'item', unit_price: 0, total: 0 }); },
            removeItem(i) { this.items.splice(i, 1); },
            updateTotal(i) { this.items[i].total = +(this.items[i].qty * this.items[i].unit_price).toFixed(2); },
            subtotal() { return this.items.reduce((s, r) => s + (+r.total || 0), 0); },
            tax()      { return +(this.subtotal() * this.taxRate / 100).toFixed(2); },
            total()    { return +(this.subtotal() + this.tax()).toFixed(2); },
            deposit()  { return +(this.total() * this.depositPct / 100).toFixed(2); },
         }">

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm space-y-1">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('quotes.store') }}" class="space-y-5">
            @csrf

            {{-- Title + Client --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quote Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. Wedding catering for 80 guests — 14 Feb"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                        <select name="customer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Select customer —</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client Email</label>
                        <input type="email" name="client_email" value="{{ old('client_email') }}"
                               placeholder="For sending the quote"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valid Until</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Required (%)</label>
                        <input type="number" name="deposit_percentage" min="0" max="100" step="5"
                               value="{{ old('deposit_percentage', 50) }}"
                               x-model.number="depositPct"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Line items --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Line Items</h3>
                    <button type="button" @click="addItem()"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Add line</button>
                </div>

                <div class="p-5 space-y-3">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="item.name" :name="'line_items['+i+'][name]'" placeholder="Description" required
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <input type="number" x-model.number="item.qty" :name="'line_items['+i+'][qty]'" min="0" step="0.5"
                                   @input="updateTotal(i)" placeholder="Qty"
                                   class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <input type="text" x-model="item.unit" :name="'line_items['+i+'][unit]'" placeholder="pax/hr/item"
                                   class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div class="relative w-28">
                                <span class="absolute inset-y-0 left-2.5 flex items-center text-gray-400 text-sm">R</span>
                                <input type="number" x-model.number="item.unit_price" :name="'line_items['+i+'][unit_price]'" min="0" step="0.01"
                                       @input="updateTotal(i)" placeholder="0.00"
                                       class="w-full border border-gray-300 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <input type="hidden" :name="'line_items['+i+'][total]'" :value="item.total">
                            <p class="w-24 text-sm font-medium text-gray-900 text-right" x-text="'R ' + item.total.toFixed(2)"></p>
                            <button type="button" @click="removeItem(i)"
                                    class="text-gray-300 hover:text-red-400 transition" x-show="items.length > 1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="px-5 pb-5 space-y-2 border-t border-gray-100 pt-4">
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Subtotal</span><span x-text="'R ' + subtotal().toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span class="flex items-center gap-2">
                            VAT / Tax
                            <input type="number" name="tax_rate" min="0" max="100" step="1"
                                   value="{{ old('tax_rate', 0) }}"
                                   x-model.number="taxRate"
                                   class="w-16 border border-gray-200 rounded px-2 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-xs">%</span>
                        </span>
                        <span x-text="'R ' + tax().toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-200 pt-2">
                        <span>Total</span><span x-text="'R ' + total().toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-sm text-indigo-600 font-medium">
                        <span>Deposit required (<span x-text="depositPct"></span>%)</span>
                        <span x-text="'R ' + deposit().toFixed(2)"></span>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Terms</label>
                <textarea name="notes" rows="3" placeholder="Payment terms, cancellation policy, inclusions/exclusions..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('quotes.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                    Save Quote
                </button>
            </div>

        </form>
    </div>
</x-app-layout>
