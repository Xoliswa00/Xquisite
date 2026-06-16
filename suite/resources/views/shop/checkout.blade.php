<x-shop-layout :tenant="$tenant" :cart="$cart">

    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

        <form action="{{ route('shop.checkout.place', $tenant->slug) }}" method="POST"
              x-data="checkoutForm()" @submit.prevent="submitForm">
            @csrf

            <div class="grid lg:grid-cols-3 gap-6">

                <!-- Left — Customer + Delivery -->
                <div class="lg:col-span-2 space-y-4">

                    <!-- Customer Details -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Your Details</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Full Name *</label>
                                <input type="text" name="customer_name" required
                                       value="{{ old('customer_name') }}"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('customer_name') border-red-400 @enderror"
                                       placeholder="Jane Smith">
                                @error('customer_name')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                <input type="tel" name="customer_phone"
                                       value="{{ old('customer_phone') }}"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                       placeholder="082 000 0000">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email Address *</label>
                                <input type="email" name="customer_email" required
                                       value="{{ old('customer_email') }}"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('customer_email') border-red-400 @enderror"
                                       placeholder="jane@example.com">
                                @error('customer_email')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Fulfillment Type -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Fulfillment</h2>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors"
                                   :class="fulfillment === 'collection' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                <input type="radio" name="fulfillment_type" value="collection"
                                       x-model="fulfillment" class="mt-0.5 accent-indigo-600">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Collection</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Pick up in store</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors"
                                   :class="fulfillment === 'delivery' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                <input type="radio" name="fulfillment_type" value="delivery"
                                       x-model="fulfillment" class="mt-0.5 accent-indigo-600">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Delivery</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Delivered to your door</p>
                                </div>
                            </label>
                        </div>

                        <!-- Address fields — shown only for delivery -->
                        <div x-show="fulfillment === 'delivery'" x-transition class="mt-4 space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Street Address *</label>
                                <input type="text" name="address_line1"
                                       :required="fulfillment === 'delivery'"
                                       value="{{ old('address_line1') }}"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('address_line1') border-red-400 @enderror"
                                       placeholder="123 Main Street">
                                @error('address_line1')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="grid sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">City *</label>
                                    <input type="text" name="address_city"
                                           :required="fulfillment === 'delivery'"
                                           value="{{ old('address_city') }}"
                                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                           placeholder="Cape Town">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Province</label>
                                    <input type="text" name="address_province"
                                           value="{{ old('address_province') }}"
                                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                           placeholder="Western Cape">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Postal Code</label>
                                    <input type="text" name="address_postal"
                                           value="{{ old('address_postal') }}"
                                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                           placeholder="8001">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Payment</h2>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors"
                                   :class="payment === 'payfast' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                <input type="radio" name="payment_method" value="payfast"
                                       x-model="payment" class="accent-indigo-600">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Pay Online</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Card, EFT, SnapScan & more via PayFast</p>
                                </div>
                                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </label>
                            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors"
                                   :class="payment === 'eft' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                <input type="radio" name="payment_method" value="eft"
                                       x-model="payment" class="accent-indigo-600">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Manual EFT</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Pay via bank transfer — we'll confirm your order once received</p>
                                </div>
                                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                            </label>
                            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors"
                                   :class="payment === 'collection' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                <input type="radio" name="payment_method" value="collection"
                                       x-model="payment" class="accent-indigo-600">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Pay on Collection</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Pay cash or card when you collect</p>
                                </div>
                                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Order Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="notes" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                                  placeholder="Special instructions or delivery notes…">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Right — Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 sticky top-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4">Order Summary</h2>

                        <div class="space-y-3 mb-4">
                            @foreach($lines as $line)
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                        @if($line->product->image_url)
                                            <img src="{{ $line->product->image_url }}" alt="{{ $line->product->name }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-900 truncate">{{ $line->product->name }}</p>
                                        <p class="text-xs text-gray-400">× {{ $line->qty }}</p>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-900 shrink-0">R{{ number_format($line->subtotal, 2) }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-100 pt-3 space-y-1.5">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Subtotal</span>
                                <span>R{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400">
                                <span>Shipping</span>
                                <span x-text="fulfillment === 'delivery' ? 'TBC' : 'N/A'"></span>
                            </div>
                            <div class="flex justify-between font-bold text-sm pt-2 border-t border-gray-100">
                                <span>Total</span>
                                <span class="text-indigo-600">R{{ number_format($subtotal, 2) }}</span>
                            </div>
                        </div>

                        <button type="submit"
                                class="mt-5 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors"
                                x-text="payment === 'payfast' ? 'Pay with PayFast →' : 'Place Order →'">
                        </button>

                        <p class="mt-3 text-center text-xs text-gray-400">
                            By placing your order you agree to our terms of service.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function checkoutForm() {
            return {
                fulfillment: '{{ old('fulfillment_type', 'collection') }}',
                payment: '{{ old('payment_method', 'payfast') }}',
                submitForm(e) {
                    this.$el.submit();
                }
            };
        }
    </script>

</x-shop-layout>
