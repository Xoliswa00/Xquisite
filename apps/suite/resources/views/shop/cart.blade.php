<x-shop-layout :tenant="$tenant" :cart="$cart">

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Your Cart</h1>

        @if($lines->isEmpty())
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-400 mb-4">Your cart is empty</p>
                <a href="{{ route('shop.index', $tenant->slug) }}"
                   class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-3 rounded-xl">
                    Continue Shopping
                </a>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4">
                @foreach($lines as $line)
                    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-0">

                        <!-- Image -->
                        <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden shrink-0">
                            @if($line->product->image_url)
                                <img src="{{ $line->product->image_url }}" alt="{{ $line->product->name }}" class="w-full h-full object-cover">
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $line->product->name }}</p>
                            <p class="text-xs text-gray-400">R{{ number_format($line->product->price, 2) }} each</p>
                        </div>

                        <!-- Qty -->
                        <form action="{{ route('shop.cart.update', $tenant->slug) }}" method="POST" class="flex items-center gap-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $line->product->id }}">
                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                <button type="submit" name="qty" value="{{ $line->qty - 1 }}"
                                        class="px-2 py-1 text-gray-400 hover:text-red-500 text-sm">−</button>
                                <span class="px-2 py-1 text-sm font-medium w-8 text-center">{{ $line->qty }}</span>
                                <button type="submit" name="qty" value="{{ $line->qty + 1 }}"
                                        class="px-2 py-1 text-gray-400 hover:text-gray-700 text-sm">+</button>
                            </div>
                        </form>

                        <!-- Subtotal -->
                        <p class="text-sm font-bold text-gray-900 w-20 text-right">R{{ number_format($line->subtotal, 2) }}</p>

                        <!-- Remove -->
                        <form action="{{ route('shop.cart.remove', $tenant->slug) }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $line->product->id }}">
                            <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Subtotal</span>
                    <span>R{{ number_format($lines->sum('subtotal'), 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-400 mb-3">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="flex justify-between font-bold text-base pt-3 border-t border-gray-100">
                    <span>Estimated Total</span>
                    <span class="text-indigo-600">R{{ number_format($lines->sum('subtotal'), 2) }}</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('shop.index', $tenant->slug) }}"
                   class="flex-1 text-center border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 rounded-xl text-sm transition-colors">
                    Continue Shopping
                </a>
                <a href="{{ route('shop.checkout', $tenant->slug) }}"
                   class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                    Proceed to Checkout
                </a>
            </div>
        @endif
    </div>

</x-shop-layout>
