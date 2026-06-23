<x-app-layout>
    <x-slot name="header">Stock History — {{ $product->name }}</x-slot>

    <div class="max-w-4xl space-y-4">

        <div class="grid lg:grid-cols-3 gap-4">

            <!-- Product info -->
            <div class="bg-slate-800 rounded-xl p-5 lg:col-span-2">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-[#D4AF37]">{{ $product->name }}</h2>
                        @if($product->sku)
                            <p class="text-xs text-slate-500 mt-0.5">SKU: {{ $product->sku }}</p>
                        @endif
                        @if($product->category)
                            <p class="text-xs text-slate-500">Category: {{ $product->category }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold {{ $product->stock_status === 'out_of_stock' ? 'text-red-400' : ($product->stock_status === 'low' ? 'text-yellow-400' : 'text-white') }}">
                            {{ $product->stock_quantity }}
                        </p>
                        <p class="text-xs text-slate-500">current stock</p>
                    </div>
                </div>
                @if($product->reorder_level > 0)
                    <div class="mt-3 flex items-center gap-3 text-sm text-slate-400">
                        <span>Reorder level: <strong class="text-white">{{ $product->reorder_level }}</strong></span>
                        @if($product->needs_reorder)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-amber-900/50 text-amber-400 border border-amber-800">
                                Reorder needed
                            </span>
                        @endif
                    </div>
                @endif
                <div class="mt-4 flex items-center gap-3">
                    <a href="{{ route('products.edit', $product) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0]">Edit product →</a>
                    @if($product->needs_reorder)
                        <a href="{{ route('purchase-orders.create', ['from_reorder' => 1]) }}" class="text-xs text-amber-400 hover:text-amber-300">Create reorder PO →</a>
                    @endif
                </div>
            </div>

            <!-- Quick adjustment -->
            <div class="bg-slate-800 rounded-xl p-5">
                <h3 class="text-sm font-medium text-slate-300 mb-3">Quick Adjustment</h3>
                <form method="POST" action="{{ route('stock.adjust', $product) }}" class="space-y-3">
                    @csrf
                    <select name="type" required
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <option value="adjustment_in">Add Stock (+)</option>
                        <option value="adjustment_out">Remove Stock (−)</option>
                    </select>
                    <input type="number" name="quantity" min="1" placeholder="Quantity" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    <input type="text" name="notes" placeholder="Reason (optional)"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    <button type="submit"
                            class="w-full bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-4 py-2 rounded-lg">
                        Apply
                    </button>
                </form>
            </div>
        </div>

        <!-- Movement history -->
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-medium text-slate-300">Movement History</h3>
                <span class="text-xs text-slate-500">{{ $adjustments->total() }} records</span>
            </div>

            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium text-right">Before</th>
                        <th class="px-4 py-3 font-medium text-right">Change</th>
                        <th class="px-4 py-3 font-medium text-right">After</th>
                        <th class="px-4 py-3 font-medium">Reference / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($adjustments as $adj)
                        @php
                            $typeColors = [
                                'sale'           => 'bg-blue-900/50 text-blue-400 border-blue-800',
                                'stocktake'      => 'bg-[#0078D4]/20 text-[#B8D4F0] border-[#0078D4]/30',
                                'receive'        => 'bg-emerald-900/50 text-emerald-400 border-emerald-800',
                                'adjustment_in'  => 'bg-teal-900/50 text-teal-400 border-teal-800',
                                'adjustment_out' => 'bg-orange-900/50 text-orange-400 border-orange-800',
                            ];
                            $cls = $typeColors[$adj->type] ?? 'bg-slate-700 text-slate-400 border-slate-600';
                        @endphp
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-slate-400 whitespace-nowrap text-xs">
                                {{ $adj->created_at->format('d M Y') }}<br>
                                <span class="text-slate-600">{{ $adj->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $cls }}">
                                    {{ $adj->label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-400">{{ $adj->quantity_before }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $adj->quantity_change >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $adj->quantity_change >= 0 ? '+' . $adj->quantity_change : $adj->quantity_change }}
                            </td>
                            <td class="px-4 py-3 text-right text-white font-medium">{{ $adj->quantity_after }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">
                                @if($adj->reference)
                                    <span class="text-slate-300 font-mono">{{ $adj->reference }}</span>
                                @endif
                                @if($adj->notes)
                                    <span class="{{ $adj->reference ? 'block' : '' }}">{{ $adj->notes }}</span>
                                @endif
                                @if(!$adj->reference && !$adj->notes)
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                No stock movements recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($adjustments->hasPages())
                <div class="px-4 py-3 border-t border-slate-700">
                    {{ $adjustments->links() }}
                </div>
            @endif
        </div>

        <a href="{{ route('products.index') }}" class="inline-block text-sm text-slate-400 hover:text-white">← Back to Products</a>
    </div>
</x-app-layout>
