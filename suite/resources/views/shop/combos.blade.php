<x-shop-layout :tenant="$tenant" :cart="$cart">

    <!-- Breadcrumb -->
    <nav class="text-xs text-gray-400 mb-6 flex items-center gap-2">
        <a href="{{ $tenant->shopRoute('index') }}" class="hover:text-[#0078D4]">Shop</a>
        <span>/</span>
        <span class="text-gray-700">Bundles</span>
    </nav>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Bundles &amp; Deals</h1>
        <p class="text-sm text-gray-500 mt-1">Buy these together and save.</p>
    </div>

    @if($combos->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm">No bundles available right now.</p>
        </div>
    @else
        <div class="grid sm:grid-cols-2 gap-5">
            @foreach($combos as $combo)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900">{{ $combo->name }}</h2>
                                @if($combo->description)
                                    <p class="text-sm text-gray-500 mt-0.5">{{ $combo->description }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-xs font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Save R{{ number_format($combo->savings, 2) }}
                            </span>
                        </div>

                        <!-- Contents -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($combo->products as $product)
                                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg pl-1 pr-2.5 py-1">
                                    <div class="w-8 h-8 rounded-md overflow-hidden bg-gray-100 shrink-0">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" onerror="shopImgFallback(this)" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">{{ $product->name }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-xs text-gray-400 line-through">R{{ number_format($combo->total_price, 2) }}</p>
                                <p class="text-2xl font-bold text-[#0078D4]">R{{ number_format($combo->combo_price, 2) }}</p>
                            </div>
                            <form action="{{ $tenant->shopRoute('cart.combo.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="combo_id" value="{{ $combo->id }}">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit"
                                        class="bg-[#0078D4] hover:bg-[#002B5B] text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition-colors">
                                    Add Bundle to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-shop-layout>
