<x-app-layout>
    <x-slot name="header">Edit Product</x-slot>

    <div class="max-w-xl space-y-4">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Product Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Category</label>
                        <input type="text" name="category" value="{{ old('category', $product->category) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Selling Price (R)</label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01" required
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Cost Price (R)</label>
                        <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" step="0.01"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Stock Quantity</label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div class="flex flex-col justify-end pb-0.5">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="track_stock" value="0">
                            <input type="checkbox" name="track_stock" id="track_stock" value="1" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}
                                   class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                            <label for="track_stock" class="text-sm text-slate-300">Track stock</label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <label for="is_active" class="text-sm text-slate-300">Available in POS</label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_available_online" value="0">
                    <input type="checkbox" name="is_available_online" id="is_available_online" value="1" {{ old('is_available_online', $product->is_available_online) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <label for="is_available_online" class="text-sm text-slate-300">Available in online store</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Product Image URL</label>
                    <input type="url" name="image_url" value="{{ old('image_url', $product->image_url) }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                           placeholder="https://…">
                    <p class="mt-1 text-xs text-slate-500">Shown in the online storefront and on receipts</p>
                </div>

                <hr class="border-slate-700">

                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Reorder Settings</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Reorder Level</label>
                        <input type="number" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" min="0"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                               placeholder="e.g. 5">
                        <p class="mt-1 text-xs text-slate-500">Alert when stock drops to this level</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Reorder Quantity</label>
                        <input type="number" name="reorder_quantity" value="{{ old('reorder_quantity', $product->reorder_quantity) }}" min="0"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                               placeholder="e.g. 20">
                        <p class="mt-1 text-xs text-slate-500">Default qty when creating a PO</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Supplier</label>
                        <input type="text" name="supplier" value="{{ old('supplier', $product->supplier) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                               placeholder="e.g. OPI Distributors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Supplier SKU</label>
                        <input type="text" name="supplier_sku" value="{{ old('supplier_sku', $product->supplier_sku) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                               placeholder="Supplier's product code">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">Save Changes</button>
                    <a href="{{ route('products.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>

        <div class="bg-slate-800 rounded-xl p-4 border border-red-900/50">
            <p class="text-sm text-slate-400 mb-3">Remove this product from the system.</p>
            <form method="POST" action="{{ route('products.destroy', $product) }}"
                  onsubmit="return confirm('Delete {{ addslashes($product->name) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg">Delete Product</button>
            </form>
        </div>
    </div>
</x-app-layout>
