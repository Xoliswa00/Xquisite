<x-app-layout>
    <x-slot name="header">Products</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name, SKU or category…"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 w-full sm:w-52 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Search</button>
                @if(request('search'))
                    <a href="{{ route('products.index') }}" class="text-sm px-4 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
                @endif
            </form>
            <a href="{{ route('products.create') }}"
               class="w-full sm:w-auto text-center bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + Add Product
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden">

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700">
                @forelse($products as $product)
                    <a href="{{ route('products.edit', $product) }}" class="block px-4 py-3 hover:bg-slate-700/50 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-white truncate">{{ $product->name }}</p>
                            @if($product->is_active)
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                            @else
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="text-xs text-slate-400">R{{ number_format($product->price, 2) }}</p>
                            @if($product->category)
                                <p class="text-xs text-slate-500">{{ $product->category }}</p>
                            @endif
                            @if($product->sku)
                                <p class="text-xs text-slate-500 font-mono">{{ $product->sku }}</p>
                            @endif
                            @if($product->track_stock)
                                <p class="text-xs {{ $product->stock_quantity <= 0 ? 'text-red-400' : ($product->stock_quantity <= 5 ? 'text-yellow-400' : 'text-slate-500') }}">
                                    Stock: {{ $product->stock_quantity }}
                                </p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-10 text-center text-slate-500 text-sm">
                        No products yet. <a href="{{ route('products.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0]">Add one.</a>
                    </div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">SKU</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Stock</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium">{{ $product->name }}</p>
                                @if($product->description)
                                    <p class="text-xs text-slate-500">{{ Str::limit($product->description, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ $product->sku ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $product->category ?? '—' }}</td>
                            <td class="px-4 py-3 text-white font-medium">R{{ number_format($product->price, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($product->track_stock)
                                    <span class="{{ $product->stock_quantity <= 0 ? 'text-red-400' : ($product->stock_quantity <= 5 ? 'text-yellow-400' : 'text-slate-300') }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($product->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('products.edit', $product) }}" class="text-slate-400 hover:text-white text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                No products yet. <a href="{{ route('products.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0]">Add one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links() }}
    </div>
</x-app-layout>
