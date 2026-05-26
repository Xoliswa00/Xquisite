<x-shop-layout :tenant="$tenant" :cart="$cart">

    <!-- Hero -->
    <div class="mb-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl px-6 py-10 text-center text-white">
        <h1 class="text-3xl font-bold">{{ $tenant->name }}</h1>
        <p class="mt-2 text-indigo-100 text-sm">Shop our full range of products online</p>
        <form action="{{ route('shop.index', $tenant->slug) }}" method="GET" class="mt-5 sm:hidden">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search products…"
                   class="w-full max-w-sm bg-white/20 border border-white/30 text-white placeholder-indigo-200 text-sm rounded-full px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-white/50">
        </form>
    </div>

    <div class="flex gap-6">

        <!-- Categories sidebar -->
        @if($categories->count())
            <aside class="hidden lg:block w-48 shrink-0">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Categories</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('shop.index', $tenant->slug) }}"
                           class="block text-sm px-3 py-2 rounded-lg {{ !$category ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                            All Products
                        </a>
                    </li>
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('shop.index', ['tenantSlug' => $tenant->slug, 'category' => $cat]) }}"
                               class="block text-sm px-3 py-2 rounded-lg {{ $category === $cat ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
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
                    <a href="{{ route('shop.index', $tenant->slug) }}"
                       class="shrink-0 text-xs px-3 py-1.5 rounded-full {{ !$category ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                        All
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('shop.index', ['tenantSlug' => $tenant->slug, 'category' => $cat]) }}"
                           class="shrink-0 text-xs px-3 py-1.5 rounded-full {{ $category === $cat ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                            {{ $cat }}
                        </a>
                    @endforeach
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
                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden group hover:shadow-md transition-shadow">
                            <a href="{{ route('shop.product', [$tenant->slug, $product->id]) }}" class="block">
                                <div class="aspect-square bg-gray-100 overflow-hidden">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            <div class="p-3">
                                @if($product->category)
                                    <p class="text-xs text-gray-400 mb-0.5">{{ $product->category }}</p>
                                @endif
                                <a href="{{ route('shop.product', [$tenant->slug, $product->id]) }}"
                                   class="text-sm font-medium text-gray-900 hover:text-indigo-600 leading-tight line-clamp-2 block">
                                    {{ $product->name }}
                                </a>
                                <p class="text-base font-bold text-indigo-600 mt-1">R{{ number_format($product->price, 2) }}</p>

                                @if($product->track_stock && $product->stock_quantity <= 0)
                                    <span class="text-xs text-red-500 font-medium">Out of stock</span>
                                @else
                                    <form action="{{ route('shop.cart.add', $tenant->slug) }}" method="POST" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit"
                                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 rounded-xl transition-colors">
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
    </div>

</x-shop-layout>
