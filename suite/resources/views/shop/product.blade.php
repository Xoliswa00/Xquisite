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

    <div class="grid md:grid-cols-2 gap-8 mb-12">

        {{--
            Gallery: the product model only has a single image_url today —
            gallery_images is a computed accessor that wraps it in a
            one-item array (see Product::getGalleryImagesAttribute) so this
            markup is already shaped for a real multi-image gallery later.
            With one image the thumbnail strip below simply never renders
            (x-show="images.length > 1"), so there's no visual change now.
            Scoped to its own small x-data — purely a client-side image
            switcher, no cart/data-flow involvement at all.
        --}}
        <div x-data="productGallery(@js($product->gallery_images))">
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                <template x-if="activeImage">
                    <img :src="activeImage" alt="{{ $product->name }}" onerror="shopImgFallback(this)" class="w-full h-full object-cover">
                </template>
                <template x-if="!activeImage">
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </template>
            </div>

            <div class="mt-3 grid grid-cols-5 gap-2" x-show="images.length > 1">
                <template x-for="(img, i) in images" :key="i">
                    <button type="button" @click="activeImage = img"
                            class="aspect-square rounded-lg overflow-hidden border-2"
                            :class="activeImage === img ? 'border-[#0078D4]' : 'border-transparent'">
                        <img :src="img" class="w-full h-full object-cover" onerror="shopImgFallback(this)">
                    </button>
                </template>
            </div>
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
            <div class="mb-4">
                <x-shop.stock-badge :product="$product" :overlay="false" />
            </div>

            {{--
                Unchanged from before this pass: a plain <form method="POST">
                to cart.add with the original vanilla-JS qty +/- stepper
                (no fetch, no Alpine, no dependency on how the cart is
                keyed). Left exactly as-is deliberately — this is the piece
                the incoming product-variant work will need to extend with
                a size/color selector, so it isn't worth restructuring twice.
            --}}
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

    <script>
        // Image gallery only — swaps the main image on thumbnail click.
        // Deliberately has nothing to do with cart/add-to-cart, which stays
        // on the original plain-form flow above.
        function productGallery(images) {
            return {
                images: images || [],
                activeImage: (images && images[0]) || null,
            };
        }
    </script>

    <!-- Related products -->
    @if($related->count())
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">More in {{ $product->category }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($related as $rel)
                    <a href="{{ $tenant->shopRoute('product', ['productId' => $rel->id]) }}"
                       class="relative bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
                        <div class="relative aspect-square bg-gray-100">
                            @if($rel->image_url)
                                <img src="{{ $rel->image_url }}" alt="{{ $rel->name }}" onerror="shopImgFallback(this)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @endif
                            @if($rel->track_stock && $rel->stock_quantity <= 5)
                                <div class="absolute top-2 left-2">
                                    <x-shop.stock-badge :product="$rel" />
                                </div>
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
