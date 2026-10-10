<x-shop-layout :tenant="$tenant" :cart="$cart">

    <!-- Hero -->
    <div class="mb-8 bg-gradient-to-r from-[#0078D4] to-[#002B5B] rounded-2xl px-6 py-10 text-center text-white">
        <h1 class="text-3xl font-bold">{{ $tenant->name }}</h1>
        <p class="mt-2 text-[#E8F2FA] text-sm">Shop our full range of products online</p>
        <form action="{{ $tenant->shopRoute('index') }}" method="GET" class="mt-5 sm:hidden">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search products…"
                   class="w-full max-w-sm bg-white/20 border border-white/30 text-white placeholder-[#B8D4F0] text-sm rounded-full px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-white/50">
        </form>
    </div>

    {{--
        Teaser only — links through to the full listing rather than
        rendering combo cards inline among products, which would mix two
        different card shapes (single product vs multi-product bundle) in
        one grid. Only rendered when there's something live to show, so a
        tenant with no bundles sees nothing here at all.
    --}}
    @if($featuredCombos->isNotEmpty())
        <div class="mb-8 bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Bundles &amp; Deals</h2>
                <a href="{{ $tenant->shopRoute('combos') }}" class="text-sm text-[#0078D4] hover:text-[#002B5B] font-medium">View all →</a>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                @foreach($featuredCombos as $combo)
                    <a href="{{ $tenant->shopRoute('combos') }}" class="block border border-gray-200 rounded-xl p-3 hover:border-[#0078D4] transition-colors">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $combo->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">R{{ number_format($combo->combo_price, 2) }} <span class="line-through">R{{ number_format($combo->total_price, 2) }}</span></p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex gap-6" x-data="shopIndex()">

        <!-- Categories sidebar -->
        @if($categories->count())
            <aside class="hidden lg:block w-48 shrink-0">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Categories</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ $tenant->shopRoute('index') }}"
                           class="block text-sm px-3 py-2 rounded-lg {{ !$category ? 'bg-[#0078D4] text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                            All Products
                        </a>
                    </li>
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ $tenant->shopRoute('index', ['category' => $cat]) }}"
                               class="block text-sm px-3 py-2 rounded-lg {{ $category === $cat ? 'bg-[#0078D4] text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                {{ $cat }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        @endif

        <!-- Product grid -->
        <div class="flex-1">

            <!-- Mobile category filter -->
            @if($categories->count())
                <div class="lg:hidden flex gap-2 overflow-x-auto pb-2 mb-4">
                    <a href="{{ $tenant->shopRoute('index') }}"
                       class="shrink-0 text-xs px-3 py-1.5 rounded-full {{ !$category ? 'bg-[#0078D4] text-white' : 'bg-gray-200 text-gray-600' }}">
                        All
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ $tenant->shopRoute('index', ['category' => $cat]) }}"
                           class="shrink-0 text-xs px-3 py-1.5 rounded-full {{ $category === $cat ? 'bg-[#0078D4] text-white' : 'bg-gray-200 text-gray-600' }}">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Sort -->
            @if($products->isNotEmpty() || $sort !== 'category')
                <div class="flex justify-end mb-4">
                    <form method="GET" x-data @change="$el.submit()">
                        @if($category) <input type="hidden" name="category" value="{{ $category }}"> @endif
                        @if($search) <input type="hidden" name="search" value="{{ $search }}"> @endif
                        <select name="sort"
                                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-600 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <option value="category" @selected($sort === 'category')>Sort: Category</option>
                            <option value="newest" @selected($sort === 'newest')>Newest</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>Price: Low to High</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>Price: High to Low</option>
                        </select>
                    </form>
                </div>
            @endif

            @if($products->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-sm">No products found.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($products as $product)
                        <div class="relative bg-white rounded-2xl border border-gray-200 overflow-hidden group hover:shadow-md transition-shadow">
                            <a href="{{ $tenant->shopRoute('product', ['productId' => $product->id]) }}" class="block">
                                <div class="relative aspect-square bg-gray-100 overflow-hidden">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                             onerror="shopImgFallback(this)"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif

                                    {{--
                                        has_variants: gate on the variant stock sum
                                        (variant_stock_sum, see StorefrontController's
                                        withSum), not the product's own stock_quantity —
                                        not authoritative once a product has variants.
                                        <x-shop.stock-badge> itself branches the same way
                                        for the number/status it actually displays.
                                    --}}
                                    @if($product->has_variants ? ($product->variant_stock_sum ?? 0) <= 5 : ($product->track_stock && $product->stock_quantity <= 5))
                                        <div class="absolute top-2 left-2">
                                            <x-shop.stock-badge :product="$product" />
                                        </div>
                                    @endif
                                </div>
                            </a>

                            {{--
                                Quick View: purely a bigger, faster look at the
                                same server-rendered data already on this
                                card — no extra request, no cart mutation.
                                Its own "Add to Cart" button below is a real
                                <form method="POST"> to the existing
                                cart.add route, identical in shape to the
                                card's own Add to Cart form beneath it.
                                Opaque by default on touch screens (no hover
                                to reveal it on) and reveal-on-hover from sm
                                up, where a pointer is the primary input.
                            --}}
                            <button type="button"
                                    @click="openQuickView({
                                        id: {{ $product->id }},
                                        name: @js($product->name),
                                        price: {{ (float) $product->price }},
                                        image_url: @js($product->image_url),
                                        category: @js($product->category),
                                        description: @js(\Illuminate\Support\Str::limit($product->description, 140)),
                                        hasVariants: {{ $product->has_variants ? 'true' : 'false' }},
                                        inStock: {{ $product->has_variants ? (($product->variant_stock_sum ?? 0) > 0 ? 'true' : 'false') : ((!$product->track_stock || $product->stock_quantity > 0) ? 'true' : 'false') }},
                                        url: @js($tenant->shopRoute('product', ['productId' => $product->id])),
                                    })"
                                    class="absolute top-2 right-2 bg-white/90 hover:bg-white text-gray-600 hover:text-[#0078D4] rounded-full p-1.5 shadow-sm sm:opacity-0 sm:group-hover:opacity-100 transition-opacity"
                                    aria-label="Quick view {{ $product->name }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>

                            <div class="p-3">
                                @if($product->category)
                                    <p class="text-xs text-gray-400 mb-0.5">{{ $product->category }}</p>
                                @endif
                                <a href="{{ $tenant->shopRoute('product', ['productId' => $product->id]) }}"
                                   class="text-sm font-medium text-gray-900 hover:text-[#0078D4] leading-tight line-clamp-2 block">
                                    {{ $product->name }}
                                </a>
                                <p class="text-base font-bold text-[#0078D4] mt-1">R{{ number_format($product->price, 2) }}</p>

                                {{--
                                    Color preview only — not a picker. Clicking
                                    through to the PDP is still required to
                                    actually choose Size/Color together (a
                                    dot here can't express "only Red is left
                                    in stock, not Blue"), same reasoning as
                                    "Select Options" below instead of a
                                    one-click add for has_variants products.
                                    strtolower() relies on the value already
                                    being a real CSS color name/keyword
                                    (Red, Navy, etc.) — a tenant who types a
                                    non-color-keyword value just gets an
                                    unstyled/transparent dot, not an error.
                                --}}
                                @php($swatches = $product->swatchColors())
                                @if(count($swatches))
                                    <div class="flex items-center gap-1 mt-1.5" title="{{ implode(', ', $swatches) }}">
                                        @foreach(array_slice($swatches, 0, 5) as $color)
                                            <span class="w-3.5 h-3.5 rounded-full border border-gray-200"
                                                  style="background-color: {{ strtolower($color) }}"></span>
                                        @endforeach
                                        @if(count($swatches) > 5)
                                            <span class="text-xs text-gray-400">+{{ count($swatches) - 5 }}</span>
                                        @endif
                                    </div>
                                @endif

                                @if($product->has_variants)
                                    {{-- Variant products need a size/color pick first — send to the product page rather than a one-click add. --}}
                                    @if(($product->variant_stock_sum ?? 0) > 0)
                                        <a href="{{ $tenant->shopRoute('product', ['productId' => $product->id]) }}"
                                           class="mt-2 block w-full text-center bg-[#0078D4] hover:bg-[#002B5B] text-white text-xs font-semibold py-2 rounded-xl transition-colors">
                                            Select Options
                                        </a>
                                    @else
                                        <span class="text-xs text-red-500 font-medium">Out of stock</span>
                                    @endif
                                @elseif($product->track_stock && $product->stock_quantity <= 0)
                                    <span class="text-xs text-red-500 font-medium">Out of stock</span>
                                @else
                                    <form action="{{ $tenant->shopRoute('cart.add') }}" method="POST" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit"
                                                class="w-full bg-[#0078D4] hover:bg-[#002B5B] text-white text-xs font-semibold py-2 rounded-xl transition-colors">
                                            Add to Cart
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- Quick View modal -->
        <div x-show="quickView" style="display: none;" x-transition.opacity
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
             @keydown.escape.window="quickView = null">
            <div class="absolute inset-0 bg-black/50" @click="quickView = null"></div>

            <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-lg max-h-[90vh] overflow-y-auto"
                 x-show="quickView" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <button type="button" @click="quickView = null"
                        class="absolute top-3 right-3 z-10 bg-white/90 hover:bg-white text-gray-500 hover:text-gray-800 rounded-full w-8 h-8 flex items-center justify-center shadow-sm"
                        aria-label="Close quick view">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <template x-if="quickView">
                    <div class="p-5 sm:p-6">
                        <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden mb-4">
                            <template x-if="quickView.image_url">
                                <img :src="quickView.image_url" :alt="quickView.name" onerror="shopImgFallback(this)" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!quickView.image_url">
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-14 h-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-[#0078D4] font-medium mb-1" x-show="quickView.category" x-text="quickView.category"></p>
                        <h3 class="text-lg font-bold text-gray-900 mb-1" x-text="quickView.name"></h3>
                        <p class="text-sm text-gray-600 leading-relaxed mb-3" x-show="quickView.description" x-text="quickView.description"></p>
                        <p class="text-2xl font-bold text-[#0078D4] mb-4" x-text="'R' + quickView.price.toFixed(2)"></p>

                        <div class="flex gap-3">
                            <a :href="quickView.url"
                               x-show="!quickView.hasVariants"
                               class="flex-1 text-center border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2.5 rounded-xl text-sm transition-colors">
                                View Details
                            </a>

                            {{--
                                A real <form method="POST"> to the same
                                cart.add route the card buttons use — a
                                normal full-page submit, not fetch. Keeps
                                quick view's add-to-cart on the exact same
                                data path as everywhere else on this page.
                                Not shown for a has_variants product — there's
                                no size/color picker in this modal, and
                                cart.add requires a variant_id for those
                                (see CartController::add); "Select Options"
                                below sends them to the product page instead,
                                same as the card's own button does.
                            --}}
                            <form x-show="quickView.inStock && !quickView.hasVariants" action="{{ $tenant->shopRoute('cart.add') }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="qty" value="1">
                                <input type="hidden" name="product_id" :value="quickView.id">
                                <button type="submit"
                                        class="w-full bg-[#0078D4] hover:bg-[#002B5B] text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                                    Add to Cart
                                </button>
                            </form>
                            <a :href="quickView.url" x-show="quickView.hasVariants && quickView.inStock"
                               class="flex-1 text-center bg-[#0078D4] hover:bg-[#002B5B] text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                                Select Options
                            </a>
                            <button type="button" x-show="!quickView.inStock" disabled
                                    class="flex-1 bg-gray-200 text-gray-400 font-semibold py-2.5 rounded-xl text-sm cursor-not-allowed">
                                Out of Stock
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
    // Quick View is display-only: it renders the exact data already on the
    // clicked card (no extra request), and its "Add to Cart" is a plain
    // <form method="POST"> to the same cart.add route every other Add to
    // Cart button on this page already uses — a normal full-page submit,
    // deliberately NOT wired to fetch/JSON. See the note in the PR
    // description for why an AJAX/live-cart version of this was pulled
    // back out.
    function shopIndex() {
        return {
            quickView: null,
            openQuickView(product) {
                this.quickView = product;
            },
        };
    }
    </script>

</x-shop-layout>
