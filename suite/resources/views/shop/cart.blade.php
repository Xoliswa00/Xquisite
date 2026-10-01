<x-shop-layout :tenant="$tenant" :cart="$cart">

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Your Cart</h1>

        @if($lines->isEmpty() && $comboLines->isEmpty())
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-400 mb-4">Your cart is empty</p>
                <a href="{{ $tenant->shopRoute('index') }}"
                   class="inline-block bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm font-semibold px-6 py-3 rounded-xl">
                    Continue Shopping
                </a>
            </div>
        @else
            {{--
                Bundle lines, own block above the plain product lines — a
                combo is conceptually one purchase (fixed contents, fixed
                combo-priced total), not N separate product rows the
                shopper could individually adjust, so it gets its own
                Remove-only row rather than the qty stepper plain lines
                have (see CartController::addCombo() — buying a second of
                the same bundle means clicking "Add Bundle to Cart" again).
            --}}
            @if($comboLines->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4">
                    @foreach($comboLines as $comboLine)
                        <div class="flex flex-wrap items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-0">
                            <div class="flex-1 min-w-[180px]">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold px-1.5 py-0.5 rounded bg-[#F0F7FF] text-[#0078D4]">Bundle</span>
                                    <p class="text-sm font-medium text-gray-900">{{ $comboLine->combo->name }}</p>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ $comboLine->combo->products->pluck('name')->join(' + ') }}</p>
                                @if($comboLine->qty > 1)
                                    <p class="text-xs text-gray-400">× {{ $comboLine->qty }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-bold text-gray-900">R{{ number_format($comboLine->subtotal, 2) }}</p>
                                <form action="{{ $tenant->shopRoute('cart.combo.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="combo_id" value="{{ $comboLine->combo->id }}">
                                    <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($lines->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4">
                @foreach($lines as $line)
                    {{--
                        flex-wrap + the actions group forced to w-full below `sm`
                        (which always starts a fresh flex line) instead of a single
                        rigid row: at 390px the image + qty stepper + subtotal +
                        remove button's fixed widths alone exceeded the available
                        width, so the product name had nowhere left to render and
                        collided with the qty stepper. Identical to the previous
                        single-row layout from `sm` up — the group only wraps once
                        there isn't room for everything on one line.
                    --}}
                    <div class="flex flex-wrap items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-0">

                        <!-- Image + Info -->
                        <div class="flex items-center gap-4 flex-1 min-w-[180px]">
                            <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden shrink-0">
                                @if($line->variant?->effectiveImageUrl() ?? $line->product->image_url)
                                    <img src="{{ $line->variant?->effectiveImageUrl() ?? $line->product->image_url }}" alt="{{ $line->product->name }}" onerror="shopImgFallback(this)" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $line->product->name }}</p>
                                @if($line->variant)
                                    <p class="text-xs text-gray-500">{{ $line->variant->label }}</p>
                                @endif
                                <p class="text-xs text-gray-400">R{{ number_format($line->unit_price, 2) }} each</p>
                            </div>
                        </div>

                        <!-- Qty + Subtotal + Remove -->
                        <div class="flex items-center justify-between gap-4 w-full sm:w-auto">
                            <form action="{{ $tenant->shopRoute('cart.update') }}" method="POST" class="flex items-center gap-1">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $line->product->id }}">
                                <input type="hidden" name="variant_id" value="{{ $line->variant?->id }}">
                                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                    <button type="submit" name="qty" value="{{ $line->qty - 1 }}"
                                            class="px-2 py-1 text-gray-400 hover:text-red-500 text-sm">−</button>
                                    <span class="px-2 py-1 text-sm font-medium w-8 text-center">{{ $line->qty }}</span>
                                    <button type="submit" name="qty" value="{{ $line->qty + 1 }}"
                                            class="px-2 py-1 text-gray-400 hover:text-gray-700 text-sm">+</button>
                                </div>
                            </form>

                            <div class="flex items-center gap-3">
                                <p class="text-sm font-bold text-gray-900 w-20 text-right">R{{ number_format($line->subtotal, 2) }}</p>
                                <form action="{{ $tenant->shopRoute('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $line->product->id }}">
                                    <input type="hidden" name="variant_id" value="{{ $line->variant?->id }}">
                                    <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            {{--
                Applying a code re-validates it server-side on every submit
                (Promotion::findUsable(), via CartService::promotion()) — it
                is never trusted from what's already in the session. The
                stored code is only a preview; placing the order re-checks
                it again regardless (see OrderService::placeOrder()), so a
                code that goes stale between here and checkout can't slip
                through either way.
            --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Promo Code</p>
                @if($promotion)
                    <div class="flex items-center justify-between gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5">
                        <span class="text-sm text-emerald-700 font-medium">{{ $promotion->code }} applied</span>
                        <form action="{{ $tenant->shopRoute('cart.promo.remove') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-emerald-700 hover:text-emerald-900 underline">Remove</button>
                        </form>
                    </div>
                @elseif($comboLines->isNotEmpty())
                    {{-- Same rule as the booking funnel: a bundle's price is already discounted, no stacking a code on top. --}}
                    <p class="text-sm text-gray-400">Promo codes can't be combined with a bundle deal — remove the bundle to use a code instead.</p>
                @else
                    <form action="{{ $tenant->shopRoute('cart.promo.apply') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="Enter code"
                               class="flex-1 min-w-0 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <button type="submit"
                                class="shrink-0 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors">
                            Apply
                        </button>
                    </form>
                @endif
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Subtotal</span>
                    <span>R{{ number_format($subtotal, 2) }}</span>
                </div>
                @if($promotion)
                    <div class="flex justify-between text-sm text-emerald-600 mb-1">
                        <span>Discount ({{ $promotion->code }})</span>
                        <span>-R{{ number_format($discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm text-gray-400 mb-3">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="flex justify-between font-bold text-base pt-3 border-t border-gray-100">
                    <span>Estimated Total</span>
                    <span class="text-[#0078D4]">R{{ number_format($subtotal - $discount, 2) }}</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ $tenant->shopRoute('index') }}"
                   class="flex-1 text-center border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 rounded-xl text-sm transition-colors">
                    Continue Shopping
                </a>
                <a href="{{ $tenant->shopRoute('checkout') }}"
                   class="flex-1 text-center bg-[#0078D4] hover:bg-[#002B5B] text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                    Proceed to Checkout
                </a>
            </div>
        @endif
    </div>

</x-shop-layout>
