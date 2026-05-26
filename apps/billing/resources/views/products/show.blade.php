<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $product->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('products.edit', $product) }}"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">Edit</a>
                <form action="{{ route('products.destroy', $product) }}" method="POST"
                      onsubmit="return confirm('Delete this product?')">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Product Details</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ $product->name }}</dd></div>
                <div><dt class="text-gray-500">Billing Type</dt><dd class="font-medium capitalize">{{ str_replace('_', ' ', $product->billing_type) }}</dd></div>
                <div><dt class="text-gray-500">Group</dt><dd class="font-medium">{{ $product->group->name ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Category</dt><dd class="font-medium">{{ $product->category->name ?? '-' }}</dd></div>
                @if($product->description)
                    <div class="col-span-2"><dt class="text-gray-500">Description</dt><dd>{{ $product->description }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Active Pricing --}}
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700">Pricing</h3>
            </div>
            @php $activePrice = $product->pricing->where('is_active', true)->first(); @endphp
            @if($activePrice)
                <dl class="grid grid-cols-3 gap-4 text-sm">
                    <div><dt class="text-gray-500">Type</dt><dd class="font-medium capitalize">{{ str_replace('_', ' ', $activePrice->pricing_type) }}</dd></div>
                    <div><dt class="text-gray-500">Price</dt><dd class="font-medium">{{ $activePrice->price ? 'R ' . number_format($activePrice->price, 2) : '-' }}</dd></div>
                    <div><dt class="text-gray-500">VAT Rate</dt><dd class="font-medium">{{ $activePrice->vat_rate }}%</dd></div>
                    @if($activePrice->min_price)
                        <div><dt class="text-gray-500">Min</dt><dd>R {{ number_format($activePrice->min_price, 2) }}</dd></div>
                        <div><dt class="text-gray-500">Max</dt><dd>R {{ number_format($activePrice->max_price, 2) }}</dd></div>
                    @endif
                </dl>

                {{-- Update price form --}}
                <div class="mt-6 pt-4 border-t">
                    <h4 class="text-sm font-semibold text-gray-600 mb-3">Set New Price</h4>
                    <form method="POST" action="{{ route('products.prices.store', $product) }}" class="flex items-end gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500">Type</label>
                            <select name="pricing_type" class="mt-1 border-gray-300 rounded text-sm">
                                @foreach(['fixed','hourly','range','per_item'] as $t)
                                    <option value="{{ $t }}" {{ $activePrice->pricing_type === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Price (R)</label>
                            <input type="number" name="price" step="0.01" min="0" value="{{ $activePrice->price }}"
                                   class="mt-1 border-gray-300 rounded text-sm w-28">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded text-sm hover:bg-slate-700">
                            Update Price
                        </button>
                    </form>
                </div>
            @else
                <p class="text-sm text-gray-400">No active price set.</p>
            @endif
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700">Included Items</h3>
            </div>
            @forelse($product->items as $item)
                <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                    <div>
                        <span class="font-medium">{{ $item->name }}</span>
                        @if($item->description) <span class="text-gray-400 ml-2">— {{ $item->description }}</span> @endif
                    </div>
                    <div class="flex items-center gap-3">
                        @if($item->is_included)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Included</span>
                        @else
                            <span class="text-xs text-gray-600">R {{ number_format($item->price, 2) }}</span>
                        @endif
                        <form action="{{ route('products.items.destroy', [$product, $item]) }}" method="POST"
                              onsubmit="return confirm('Remove item?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 text-xs">&times; Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No items yet.</p>
            @endforelse

            <form method="POST" action="{{ route('products.items.store', $product) }}" class="mt-4 pt-4 border-t">
                @csrf
                <h4 class="text-sm font-semibold text-gray-600 mb-3">Add Item</h4>
                <div class="grid grid-cols-4 gap-3 items-end">
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500">Item Name *</label>
                        <input type="text" name="name" required
                               class="mt-1 w-full border-gray-300 rounded text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Price (R)</label>
                        <input type="number" name="price" step="0.01" min="0"
                               class="mt-1 w-full border-gray-300 rounded text-sm">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="is_included" value="1"> Included
                        </label>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded text-sm hover:bg-slate-700">
                        Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
