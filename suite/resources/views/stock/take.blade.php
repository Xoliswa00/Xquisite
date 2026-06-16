<x-app-layout>
    <x-slot name="header">Stock Take</x-slot>

    <div class="max-w-4xl space-y-4">

        <div class="bg-slate-800/50 border border-slate-700 rounded-xl px-5 py-4 text-sm text-slate-300">
            Enter the <strong class="text-white">physical count</strong> you find on the shelf for each product.
            Leave a field blank to skip that product. The system will calculate the variance and update all levels when you save.
        </div>

        <form method="POST" action="{{ route('stock.take.save') }}" x-data="{ changed: false }">
            @csrf

            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Tracked Products</h3>
                    <span class="text-xs text-slate-500">{{ $products->count() }} products</span>
                </div>

                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[600px]">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 text-left">
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium text-right">System Qty</th>
                            <th class="px-4 py-3 font-medium text-right">Reorder At</th>
                            <th class="px-4 py-3 font-medium text-center w-36">Physical Count</th>
                            <th class="px-4 py-3 font-medium text-right">Variance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700" x-data="stockTake()">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-700/30" x-data="{ systemQty: {{ $product->stock_quantity }}, physicalQty: null }"
                                :class="physicalQty !== null && physicalQty !== '' && parseInt(physicalQty) !== systemQty ? 'bg-yellow-900/10' : ''">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-white">{{ $product->name }}</p>
                                    @if($product->sku)
                                        <p class="text-xs text-slate-500">{{ $product->sku }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ $product->category ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="{{ $product->stock_status === 'out_of_stock' ? 'text-red-400' : ($product->stock_status === 'low' ? 'text-yellow-400' : 'text-white') }} font-medium">
                                        {{ $product->stock_quantity }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-400">
                                    {{ $product->reorder_level > 0 ? $product->reorder_level : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number"
                                           name="counts[{{ $product->id }}]"
                                           x-model="physicalQty"
                                           @change="changed = true"
                                           min="0"
                                           placeholder="{{ $product->stock_quantity }}"
                                           class="w-24 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-1.5 text-center focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    <template x-if="physicalQty !== null && physicalQty !== ''">
                                        <span :class="parseInt(physicalQty) > systemQty ? 'text-emerald-400' : (parseInt(physicalQty) < systemQty ? 'text-red-400' : 'text-slate-500')">
                                            <span x-text="parseInt(physicalQty) >= systemQty ? '+' + (parseInt(physicalQty) - systemQty) : (parseInt(physicalQty) - systemQty)"></span>
                                        </span>
                                    </template>
                                    <template x-if="physicalQty === null || physicalQty === ''">
                                        <span class="text-slate-600">—</span>
                                    </template>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                    No tracked products. Enable "Track stock" on your products first.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->count())
                <div class="flex items-start gap-4 mt-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Notes (optional)</label>
                        <input type="text" name="notes" placeholder="e.g. Monthly stock take — June 2026"
                               class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div class="pt-6">
                        <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">
                            Save Stock Take
                        </button>
                    </div>
                </div>
            @endif
        </form>

        <!-- Legend -->
        <div class="flex items-center gap-6 text-xs text-slate-500">
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>Low / At reorder level</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Out of stock</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>Variance positive</span>
        </div>
    </div>
</x-app-layout>
