<x-app-layout>
    <x-slot name="header">New Purchase Order</x-slot>

    <div class="max-w-4xl" x-data="createPO()" x-init="calcTotal()">

        @if($preloadProducts->count())
            <div class="mb-4 bg-amber-900/20 border border-amber-700/50 rounded-xl px-5 py-3 text-sm text-amber-300">
                Pre-filled with <strong>{{ $preloadProducts->count() }}</strong> products below reorder level. Adjust quantities and costs before saving.
            </div>
        @endif

        <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-4">
            @csrf

            @if($errors->any())
                <div class="bg-red-900/30 border border-red-700 rounded-xl px-5 py-4 text-sm text-red-300">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Supplier details -->
            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-medium text-slate-300">
                    Supplier <span class="text-slate-500 font-normal">(optional)</span>
                </h3>

                @if($suppliers->count())
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Select Supplier</label>
                        <select name="supplier_id"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">— Select from saved suppliers —</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                    {{ $sup->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-600 mt-1">Or fill in free-text below to use a new supplier</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Supplier Name (free text)</label>
                        <input type="text" name="supplier" value="{{ old('supplier') }}"
                               placeholder="e.g. OPI Distributors"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Contact / Email</label>
                        <input type="text" name="supplier_contact" value="{{ old('supplier_contact') }}"
                               placeholder="e.g. orders@supplier.co.za"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Line items -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Order Items</h3>
                    <button type="button" @click="addItem()"
                            class="text-xs text-indigo-400 hover:text-indigo-300">
                        + Add Line
                    </button>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 text-left">
                            <th class="px-4 py-2 font-medium">Product</th>
                            <th class="px-4 py-2 font-medium text-right w-28">Qty</th>
                            <th class="px-4 py-2 font-medium text-right w-36">Unit Cost (R)</th>
                            <th class="px-4 py-2 font-medium text-right w-32">Subtotal</th>
                            <th class="px-4 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="border-b border-slate-700">
                                <td class="px-4 py-2">
                                    <select :name="'items[' + index + '][product_id]'"
                                            x-model="item.product_id"
                                            @change="onProductChange(item)"
                                            required
                                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">Select product…</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id" x-text="p.name" :selected="item.product_id == p.id"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number"
                                           :name="'items[' + index + '][qty]'"
                                           x-model="item.qty"
                                           @input="updateSubtotal(item)"
                                           min="1" required
                                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-1.5 text-right focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number"
                                           :name="'items[' + index + '][unit_cost]'"
                                           x-model="item.unit_cost"
                                           @input="updateSubtotal(item)"
                                           min="0" step="0.01" required
                                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-1.5 text-right focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-2 text-right text-white font-medium">
                                    R <span x-text="parseFloat(item.subtotal || 0).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="removeItem(index)"
                                            x-show="items.length > 1"
                                            class="text-slate-600 hover:text-red-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-600">
                            <td colspan="3" class="px-4 py-3 text-right text-slate-400 text-sm font-medium">
                                Estimated Total
                            </td>
                            <td class="px-4 py-3 text-right text-xl font-bold text-white">
                                R <span x-text="total.toFixed(2)"></span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes + submit -->
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-400 mb-1">Notes (optional)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}"
                           placeholder="e.g. Monthly restock — June 2026"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="pt-5 flex items-center gap-3">
                    <a href="{{ route('purchase-orders.index') }}" class="text-sm text-slate-400 hover:text-white">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">
                        Create Order
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
    function createPO() {
        const products = @json($allProducts->map(fn($p) => [
            'id'               => $p->id,
            'name'             => $p->name,
            'cost_price'       => (float) $p->cost_price,
            'reorder_quantity' => (int) $p->reorder_quantity,
        ]));

        const preloaded = @json($preloadProducts->map(fn($p) => [
            'product_id' => $p->id,
            'qty'        => max(1, (int) $p->reorder_quantity),
            'unit_cost'  => (float) $p->cost_price,
            'subtotal'   => max(1, (int) $p->reorder_quantity) * (float) $p->cost_price,
        ]));

        return {
            products,
            items: preloaded.length
                ? preloaded
                : [{ product_id: '', qty: 1, unit_cost: 0, subtotal: 0 }],
            total: 0,

            addItem() {
                this.items.push({ product_id: '', qty: 1, unit_cost: 0, subtotal: 0 });
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.calcTotal();
            },

            onProductChange(item) {
                const p = this.products.find(p => p.id == item.product_id);
                if (p && p.cost_price > 0) {
                    item.unit_cost = p.cost_price;
                }
                if (p && p.reorder_quantity > 0 && item.qty === 1) {
                    item.qty = p.reorder_quantity;
                }
                this.updateSubtotal(item);
            },

            updateSubtotal(item) {
                item.subtotal = parseFloat(item.qty || 0) * parseFloat(item.unit_cost || 0);
                this.calcTotal();
            },

            calcTotal() {
                this.total = this.items.reduce(
                    (sum, i) => sum + (parseFloat(i.subtotal) || 0), 0
                );
            },
        };
    }
    </script>

</x-app-layout>
