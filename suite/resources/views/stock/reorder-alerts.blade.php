<x-app-layout>
    <x-slot name="header">Reorder Alerts</x-slot>

    <div class="max-w-4xl space-y-4">

        @if($products->count())
            <div class="bg-amber-900/20 border border-amber-700/50 rounded-xl px-5 py-4 flex flex-wrap items-center gap-3">
                <div class="text-sm text-amber-300 flex-1">
                    <strong>{{ $products->count() }} {{ $products->count() === 1 ? 'product' : 'products' }}</strong>
                    at or below reorder level. Create a purchase order to restock.
                </div>
                <a href="{{ route('purchase-orders.create', ['from_reorder' => 1]) }}"
                   class="bg-amber-600 hover:bg-amber-500 text-white text-sm px-5 py-2 rounded-lg whitespace-nowrap">
                    Create PO for All
                </a>
            </div>
        @else
            <div class="bg-emerald-900/20 border border-emerald-700/50 rounded-xl px-5 py-4 text-sm text-emerald-300">
                All products are above their reorder levels. No action needed right now.
            </div>
        @endif

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-medium text-slate-300">Products Needing Restock</h3>
                <a href="{{ route('purchase-orders.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">View all POs →</a>
            </div>

            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[680px]">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium text-right">Current Qty</th>
                        <th class="px-4 py-3 font-medium text-right">Reorder At</th>
                        <th class="px-4 py-3 font-medium text-right">Suggest Order</th>
                        <th class="px-4 py-3 font-medium">Supplier</th>
                        <th class="px-4 py-3 font-medium text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <p class="font-medium text-white">{{ $product->name }}</p>
                                @if($product->sku)
                                    <p class="text-xs text-slate-500">{{ $product->sku }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $product->category ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $product->stock_quantity <= 0 ? 'text-red-400' : 'text-yellow-400' }}">
                                {{ $product->stock_quantity }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-400">{{ $product->reorder_level }}</td>
                            <td class="px-4 py-3 text-right text-slate-300">
                                {{ $product->reorder_quantity > 0 ? $product->reorder_quantity : '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $product->supplier ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('stock.history', $product) }}"
                                   class="text-xs text-indigo-400 hover:text-indigo-300">History</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                No products need reordering.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

    </div>
</x-app-layout>
