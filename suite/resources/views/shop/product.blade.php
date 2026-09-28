<x-shop-layout :tenant="$tenant" :cart="$cart">

    <!-- Breadcrumb -->
    <nav class="text-xs text-gray-400 mb-6 flex items-center gap-2">
        <a href="{{ $tenant->shopRoute('index') }}" class="hover:text-[#0078D4]">Shop</a>
        <span>/</span>
        @if($product->category)
            <a href="{{ $tenant->shopRoute('index', ['category' => $product->category]) }}" class="hover:text-[#0078D4]">{{ $product->category }}</a>
            <span>/</span>
        @endif
        <span class="text-gray-700">{{ $product->name }}</span>
    </nav>

    @if($product->has_variants)
        @php
            $variantsForJs = $product->activeVariants->map(fn ($v) => [
                'id' => $v->id, 'attributes' => $v->attributes,
                'price' => $v->effectivePrice(), 'stock' => $v->stock_quantity,
                'track_stock' => $v->track_stock, 'image_url' => $v->effectiveImageUrl(),
                'sku' => $v->sku,
            ])->values();
        @endphp
        <div x-data="variantPicker(@js($product->variant_options ?? []), @js($variantsForJs), @js($product->image_url))"
             class="grid md:grid-cols-2 gap-8 mb-12">

            <!-- Image -->
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                <template x-if="image">
                    <img :src="image" :alt="@js($product->name)" onerror="shopImgFallback(this)" class="w-full h-full object-cover">
                </template>
                <template x-if="!image">
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </template>
            </div>

            <!-- Details -->
            <div class="flex flex-col">
                @if($product->category)
                    <p class="text-sm text-[#0078D4] font-medium mb-1">{{ $product->category }}</p>
                @endif
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>

                <p class="text-xs text-gray-400 mb-3" x-show="sku" x-text="'SKU: ' + sku"></p>

                <p class="text-3xl font-bold text-[#0078D4] mb-4" x-text="'R' + price.toFixed(2)"></p>

                @if($product->description)
                    <p class="text-sm text-gray-600 leading-relaxed mb-6">{{ $product->description }}</p>
                @endif

                <!-- Option pickers -->
                <template x-for="optionName in optionNames" :key="optionName">
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2" x-text="optionName"></p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="value in options[optionName]" :key="value">
                                <button type="button"
                                        @click="choose(optionName, value)"
                                        :disabled="!isAvailable(optionName, value)"
                                        :class="{
                                            'border-[#0078D4] bg-[#F0F7FF] text-[#0078D4]': selected[optionName] === value,
                                            'border-gray-200 text-gray-700 hover:border-gray-300': selected[optionName] !== value && isAvailable(optionName, value),
                                            'border-gray-100 text-gray-300 cursor-not-allowed line-through': !isAvailable(optionName, value),
                                        }"
                                        class="px-3 py-2 text-sm font-medium border-2 rounded-lg transition-colors"
                                        x-text="value"></button>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Stock -->
                <div class="mb-4">
                    <template x-if="!selectedVariant">
                        <p class="text-sm text-gray-400">Select options to see availability</p>
                    </template>
                    <template x-if="selectedVariant && selectedVariant.track_stock && selectedVariant.stock <= 0">
                        <div class="inline-flex items-center gap-1.5 text-sm text-red-600 font-medium">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span> Out of Stock
                        </div>
                    </template>
                    <template x-if="selectedVariant && selectedVariant.track_stock && selectedVariant.stock > 0 && selectedVariant.stock <= 5">
                        <div class="inline-flex items-center gap-1.5 text-sm text-amber-600 font-medium">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                            <span x-text="'Only ' + selectedVariant.stock + ' left'"></span>
                        </div>
                    </template>
                    <template x-if="selectedVariant && (!selectedVariant.track_stock || selectedVariant.stock > 5)">
                        <div class="inline-flex items-center gap-1.5 text-sm text-emerald-600 font-medium">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> In Stock
                        </div>
                    </template>
                </div>

                <form action="{{ $tenant->shopRoute('cart.add') }}" method="POST" class="flex gap-3 items-center">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" :value="selectedVariant ? selectedVariant.id : ''">
                    <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                        <button type="button" @click="qty = Math.max(1, qty - 1)"
                                class="px-3 py-2.5 text-gray-500 hover:bg-gray-50 text-lg leading-none">−</button>
                        <input type="number" name="qty" x-model.number="qty" min="1" :max="maxQty"
                               class="w-14 text-center border-0 focus:ring-0 text-sm font-medium">
                        <button type="button" @click="qty = Math.min(maxQty, qty + 1)"
                                class="px-3 py-2.5 text-gray-500 hover:bg-gray-50 text-lg leading-none">+</button>
                    </div>
                    <button type="submit" :disabled="!canAddToCart"
                            :class="canAddToCart ? 'bg-[#0078D4] hover:bg-[#002B5B] cursor-pointer' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                            class="flex-1 text-white font-semibold py-3 px-6 rounded-xl transition-colors"
                            x-text="!selectedVariant ? 'Select Options' : (selectedVariant.track_stock && selectedVariant.stock <= 0 ? 'Out of Stock' : 'Add to Cart')">
                    </button>
                </form>

                <a href="{{ $tenant->shopRoute('cart') }}" class="mt-3 text-center text-sm text-[#0078D4] hover:text-[#002B5B]">
                    View Cart ({{ $cart->count() }} items)
                </a>
            </div>
        </div>
    @else
        <div class="grid md:grid-cols-2 gap-8 mb-12">

            <!-- Image -->
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" onerror="shopImgFallback(this)" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Details -->
            <div class="flex flex-col">
                @if($product->category)
                    <p class="text-sm text-[#0078D4] font-medium mb-1">{{ $product->category }}</p>
                @endif
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>

                @if($product->sku)
                    <p class="text-xs text-gray-400 mb-3">SKU: {{ $product->sku }}</p>
                @endif

                <p class="text-3xl font-bold text-[#0078D4] mb-4">R{{ number_format($product->price, 2) }}</p>

                @if($product->description)
                    <p class="text-sm text-gray-600 leading-relaxed mb-6">{{ $product->description }}</p>
                @endif

                <!-- Stock -->
                @if($product->track_stock)
                    @if($product->stock_quantity <= 0)
                        <div class="inline-flex items-center gap-1.5 text-sm text-red-600 font-medium mb-4">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span> Out of Stock
                        </div>
                    @elseif($product->stock_quantity <= 5)
                        <div class="inline-flex items-center gap-1.5 text-sm text-amber-600 font-medium mb-4">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span> Only {{ $product->stock_quantity }} left
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 text-sm text-emerald-600 font-medium mb-4">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> In Stock
                        </div>
                    @endif
                @endif

                @if(!$product->track_stock || $product->stock_quantity > 0)
                    <form action="{{ $tenant->shopRoute('cart.add') }}" method="POST" class="flex gap-3 items-center">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden">
                            <button type="button" onclick="const q=document.getElementById('qty');q.value=Math.max(1,parseInt(q.value)-1)"
                                    class="px-3 py-2.5 text-gray-500 hover:bg-gray-50 text-lg leading-none">−</button>
                            <input id="qty" type="number" name="qty" value="1" min="1" max="{{ $product->track_stock ? $product->stock_quantity : 99 }}"
                                   class="w-14 text-center border-0 focus:ring-0 text-sm font-medium">
                            <button type="button" onclick="const q=document.getElementById('qty');q.value=Math.min({{ $product->track_stock ? $product->stock_quantity : 99 }},parseInt(q.value)+1)"
                                    class="px-3 py-2.5 text-gray-500 hover:bg-gray-50 text-lg leading-none">+</button>
                        </div>
                        <button type="submit"
                                class="flex-1 bg-[#0078D4] hover:bg-[#002B5B] text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                            Add to Cart
                        </button>
                    </form>
                @else
                    <button disabled class="w-full bg-gray-200 text-gray-400 font-semibold py-3 rounded-xl cursor-not-allowed">
                        Out of Stock
                    </button>
                @endif

                <a href="{{ $tenant->shopRoute('cart') }}" class="mt-3 text-center text-sm text-[#0078D4] hover:text-[#002B5B]">
                    View Cart ({{ $cart->count() }} items)
                </a>
            </div>
        </div>
    @endif

    <!-- Related products -->
    @if($related->count())
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">More in {{ $product->category }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($related as $rel)
                    <a href="{{ $tenant->shopRoute('product', ['productId' => $rel->id]) }}"
                       class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
                        <div class="aspect-square bg-gray-100">
                            @if($rel->image_url)
                                <img src="{{ $rel->image_url }}" alt="{{ $rel->name }}" onerror="shopImgFallback(this)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @endif
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 leading-tight line-clamp-2">{{ $rel->name }}</p>
                            <p class="text-sm font-bold text-[#0078D4] mt-1">R{{ number_format($rel->price, 2) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</x-shop-layout>
