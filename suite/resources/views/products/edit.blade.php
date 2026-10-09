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
                    {{--
                        getRawOriginal(), not $product->image_url — that
                        accessor is overridden (Product::getImageUrlAttribute())
                        to resolve to the uploaded cover photo once one
                        exists. Reading the override here would mean
                        re-saving this form unchanged silently overwrites
                        the real stored column with the resolved photo URL
                        string. This field is specifically the legacy
                        fallback value — see the Photos section below for
                        the real upload.
                    --}}
                    <input type="url" name="image_url" value="{{ old('image_url', $product->getRawOriginal('image_url')) }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                           placeholder="https://…">
                    <p class="mt-1 text-xs text-slate-500">Fallback only — used while no photos are uploaded below.</p>
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
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-6 py-2 rounded-lg">Save Changes</button>
                    <a href="{{ route('products.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>

        {{-- ── Photos ─────────────────────────────────────────────────────── --}}
        @php
            $maxPhotos = \App\Modules\POS\Models\Product::MAX_PHOTOS;
            $photos    = $product->photosOrdered;
            $slotsLeft = $maxPhotos - $photos->count();
        @endphp
        <div id="photos" class="bg-slate-800 rounded-xl p-6 space-y-4 scroll-mt-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-200">Photos</h3>
                <p class="text-xs text-slate-500 mt-0.5">Up to {{ $maxPhotos }} photos. The cover is the one customers see in the online store and POS.</p>
                <p class="text-xs text-amber-300/80 mt-1">Each photo action here saves on its own — you don't need to press "Save Changes".</p>
            </div>

            @if($photos->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($photos as $photo)
                        <div class="relative rounded-lg overflow-hidden border {{ $photo->is_primary ? 'border-[#0078D4] ring-1 ring-[#0078D4]' : ($photo->isHidden() ? 'border-red-800' : 'border-slate-700') }}">
                            <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->alt_text }}" loading="lazy"
                                 width="300" height="300" class="w-full aspect-square object-cover bg-slate-900 {{ $photo->isHidden() ? 'opacity-40' : '' }}">

                            @if($photo->isHidden())
                                <span class="absolute inset-x-1.5 bottom-1.5 bg-red-900/90 text-red-100 text-[10px] font-semibold px-2 py-1 rounded text-center">
                                    Hidden by moderation
                                </span>
                            @endif

                            @if($photo->is_primary)
                                <span class="absolute top-1.5 left-1.5 bg-[#0078D4] text-white text-[10px] font-bold px-2 py-0.5 rounded">Cover</span>
                            @elseif(! $photo->isHidden())
                                <form method="POST" action="{{ route('products.photos.primary', [$product, $photo]) }}" class="absolute top-1.5 left-1.5">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="bg-slate-900/80 hover:bg-[#0078D4] text-white text-[10px] font-semibold px-2 py-0.5 rounded transition-colors">Make cover</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('products.photos.destroy', [$product, $photo]) }}" class="absolute top-1.5 right-1.5"
                                  onsubmit="return confirm('Remove this photo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-6 h-6 rounded bg-slate-900/80 hover:bg-red-600 text-white flex items-center justify-center transition-colors" aria-label="Remove photo">&times;</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500">No photos yet.</p>
            @endif

            @if($slotsLeft > 0)
                <form method="POST" action="{{ route('products.photos.store', $product) }}" enctype="multipart/form-data" class="space-y-2 border-t border-slate-700 pt-4">
                    @csrf
                    <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" required
                           class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#0078D4] file:text-white hover:file:bg-[#0065B8] file:cursor-pointer">
                    <p class="text-xs text-slate-500">JPG, PNG or WebP · max 4MB each · {{ $slotsLeft }} slot{{ $slotsLeft === 1 ? '' : 's' }} left.</p>
                    <p class="text-xs text-slate-500">iPhone: if a photo won't upload, set Camera → Formats → "Most Compatible", or share it first (that converts it to JPG).</p>
                    @error('photos')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                    @error('photos.*')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                    <button type="submit" class="bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm px-4 py-2 rounded-lg transition-colors">Upload photos</button>
                </form>
            @else
                <p class="text-xs text-slate-500 border-t border-slate-700 pt-4">Photo limit reached. Remove one to add another.</p>
            @endif
        </div>

        <div class="bg-slate-800 rounded-xl p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-100">Variants (Size, Color, etc.)</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        @if($product->has_variants)
                            {{ $product->variants()->count() }} variant(s) — each tracks its own stock and can override the price above.
                        @else
                            Sell this product in multiple sizes/colors/etc., each with its own stock.
                        @endif
                    </p>
                </div>
                <a href="{{ route('products.variants.index', $product) }}"
                   class="shrink-0 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-4 py-2 rounded-lg">
                    {{ $product->has_variants ? 'Manage Variants' : 'Add Variants' }}
                </a>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl p-4 border border-red-900/50">
            <p class="text-sm text-slate-400 mb-3">Remove this product from the system.</p>
            <form method="POST" action="{{ route('products.destroy', $product) }}"
                  onsubmit="return confirm(@js('Delete ' . $product->name . '?'))">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg">Delete Product</button>
            </form>
        </div>
    </div>
</x-app-layout>
