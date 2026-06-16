<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Terminal — Xquisite</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-950 text-slate-100 h-screen overflow-hidden">

<div x-data="pos()" class="h-screen flex flex-col">

    <!-- Header bar -->
    <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white text-sm">← Back</a>
            <span class="text-white font-semibold">POS Terminal</span>
            @if($appointment)
                <span class="text-xs bg-indigo-900/50 text-indigo-300 border border-indigo-800 px-2 py-0.5 rounded-full">
                    Appointment #{{ $appointment->id }} · {{ $appointment->customer->name }}
                </span>
            @endif
        </div>
        <a href="{{ route('pos.sales.index') }}" class="text-sm text-slate-400 hover:text-white">Sales History →</a>
    </header>

    <!-- Main split layout -->
    <div class="flex flex-col md:flex-row flex-1 overflow-hidden">

        <!-- LEFT: Product catalog -->
        <div class="flex-1 min-h-0 flex flex-col border-b md:border-b-0 md:border-r border-slate-800 overflow-hidden">

            <!-- Search -->
            <div class="p-4 border-b border-slate-800">
                <input type="text"
                       x-model="search"
                       placeholder="Search products…"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-4 py-2.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Suggested products for this service -->
            @if($serviceSuggestions)
                <div class="px-4 py-3 border-b border-slate-800 bg-indigo-950/30">
                    <p class="text-xs text-indigo-400 font-medium mb-2">
                        Suggested for {{ $appointment->services->pluck('name')->join(', ') }}
                    </p>
                    <div class="flex gap-2 overflow-x-auto">
                        <template x-for="p in suggestions" :key="p.id">
                            <button @click="addProduct(p)"
                                    class="shrink-0 bg-indigo-900/40 hover:bg-indigo-900/70 border border-indigo-800 rounded-lg px-3 py-2 text-left transition-colors">
                                <p class="text-xs text-white font-medium" x-text="p.name"></p>
                                <p class="text-xs text-indigo-300 font-bold mt-0.5" x-text="'R' + p.price.toFixed(2)"></p>
                            </button>
                        </template>
                    </div>
                </div>
            @endif

            <!-- Category tabs -->
            <div class="flex gap-2 px-4 py-3 border-b border-slate-800 overflow-x-auto">
                <button @click="activeCategory = ''"
                        :class="activeCategory === '' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                        class="shrink-0 text-xs px-3 py-1.5 rounded-lg">
                    All
                </button>
                <template x-for="cat in categories" :key="cat">
                    <button @click="activeCategory = cat"
                            :class="activeCategory === cat ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                            class="shrink-0 text-xs px-3 py-1.5 rounded-lg"
                            x-text="cat">
                    </button>
                </template>
            </div>

            <!-- Product grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button @click="addProduct(product)"
                                class="bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-indigo-600 rounded-xl p-3 text-left transition-colors group">
                            <p class="text-xs text-slate-400 mb-1" x-text="product.category"></p>
                            <p class="text-sm font-medium text-white leading-tight" x-text="product.name"></p>
                            <p class="text-sm font-bold text-emerald-400 mt-1.5" x-text="'R' + product.price.toFixed(2)"></p>
                            <template x-if="product.tracked">
                                <p class="text-xs text-slate-500 mt-0.5" x-text="'Stock: ' + product.stock"></p>
                            </template>
                        </button>
                    </template>
                    <template x-if="filteredProducts.length === 0">
                        <div class="col-span-4 py-12 text-center text-slate-500 text-sm">
                            No products match your search.
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- RIGHT: Order panel -->
        <div class="md:w-96 md:shrink-0 h-[45vh] md:h-auto flex flex-col bg-slate-900 overflow-hidden">

            <!-- Customer info -->
            @if($appointment)
                <div class="px-4 py-3 border-b border-slate-800 bg-slate-800/50">
                    <p class="text-xs text-slate-400">Customer</p>
                    <p class="text-sm font-medium text-white">{{ $appointment->customer->name }}</p>
                    @if($appointment->customer->phone)
                        <p class="text-xs text-slate-500">{{ $appointment->customer->phone }}</p>
                    @endif
                </div>
            @endif

            <!-- Order items -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-4">
                    <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-3">Order Items</h3>

                    <template x-if="items.length === 0">
                        <div class="py-8 text-center text-slate-600 text-sm">Add items from the catalog</div>
                    </template>

                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="bg-slate-800 rounded-lg p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs px-1.5 py-0.5 rounded"
                                                  :class="item.type === 'service' ? 'bg-indigo-900/50 text-indigo-300' : 'bg-slate-700 text-slate-300'"
                                                  x-text="item.type === 'service' ? 'Service' : 'Product'">
                                            </span>
                                        </div>
                                        <p class="text-sm font-medium text-white mt-1" x-text="item.name"></p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            R<span x-text="item.unit_price.toFixed(2)"></span> each
                                        </p>
                                    </div>
                                    <button @click="removeItem(index)"
                                            class="text-slate-600 hover:text-red-400 text-lg leading-none shrink-0 mt-1">×</button>
                                </div>

                                <!-- Quantity control -->
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-2">
                                        <button @click="updateQty(index, item.qty - 1)"
                                                class="w-6 h-6 rounded bg-slate-700 hover:bg-slate-600 text-sm flex items-center justify-center">−</button>
                                        <span class="text-sm w-6 text-center" x-text="item.qty"></span>
                                        <button @click="updateQty(index, item.qty + 1)"
                                                class="w-6 h-6 rounded bg-slate-700 hover:bg-slate-600 text-sm flex items-center justify-center">+</button>
                                    </div>
                                    <p class="text-sm font-bold text-white">
                                        R<span x-text="item.subtotal.toFixed(2)"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Totals + checkout -->
            <div class="border-t border-slate-800 p-4 space-y-3">

                <!-- Discount -->
                <div class="flex items-center gap-3">
                    <label class="text-xs text-slate-400 shrink-0">Discount (R)</label>
                    <input type="number" x-model.number="discount" min="0" step="0.01"
                           class="flex-1 bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Totals -->
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal</span>
                        <span>R<span x-text="subtotal.toFixed(2)"></span></span>
                    </div>
                    <template x-if="discount > 0">
                        <div class="flex justify-between text-emerald-400">
                            <span>Discount</span>
                            <span>−R<span x-text="discount.toFixed(2)"></span></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-white font-bold text-base pt-1 border-t border-slate-800">
                        <span>TOTAL</span>
                        <span>R<span x-text="total.toFixed(2)"></span></span>
                    </div>
                </div>

                <!-- Payment method -->
                <div>
                    <p class="text-xs text-slate-400 mb-2">Payment Method</p>
                    <div class="grid grid-cols-4 gap-1.5">
                        <template x-for="method in ['cash','card','eft','split']" :key="method">
                            <button @click="paymentMethod = method"
                                    :class="paymentMethod === method ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-800 text-slate-400 border-slate-700'"
                                    class="border text-xs py-2 rounded-lg capitalize">
                                <span x-text="method.toUpperCase()"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Notes -->
                <textarea x-model="notes" rows="2"
                          placeholder="Notes (optional)"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none"></textarea>

                <!-- Checkout button -->
                <form id="checkout-form" method="POST" action="{{ route('pos.checkout') }}">
                    @csrf
                    @if($appointment)
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        <input type="hidden" name="customer_id" value="{{ $appointment->customer_id }}">
                    @endif
                    <input type="hidden" name="payment_method" x-bind:value="paymentMethod">
                    <input type="hidden" name="discount" x-bind:value="discount">
                    <input type="hidden" name="notes" x-bind:value="notes">
                    <div id="items-container"></div>

                    <button type="button"
                            @click="submitCheckout()"
                            :disabled="items.length === 0"
                            :class="items.length === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-500'"
                            class="w-full bg-emerald-600 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                        Process Payment · R<span x-text="total.toFixed(2)"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function pos() {
    return {
        search: '',
        activeCategory: '',
        paymentMethod: 'cash',
        discount: 0,
        notes: '',
        items: @json($preloadItems),
        allProducts: @json($products),
        suggestions: @json($serviceSuggestions),

        get categories() {
            return [...new Set(this.allProducts.map(p => p.category))].filter(Boolean).sort();
        },

        get filteredProducts() {
            return this.allProducts.filter(p => {
                const matchSearch = !this.search ||
                    p.name.toLowerCase().includes(this.search.toLowerCase()) ||
                    (p.sku && p.sku.toLowerCase().includes(this.search.toLowerCase()));
                const matchCat = !this.activeCategory || p.category === this.activeCategory;
                return matchSearch && matchCat;
            });
        },

        get subtotal() {
            return this.items.reduce((sum, i) => sum + i.subtotal, 0);
        },

        get total() {
            return Math.max(0, this.subtotal - this.discount);
        },

        addProduct(product) {
            const existing = this.items.findIndex(i => i.type === 'product' && i.id === product.id);
            if (existing >= 0) {
                this.updateQty(existing, this.items[existing].qty + 1);
            } else {
                this.items.push({
                    id:         product.id,
                    type:       'product',
                    name:       product.name,
                    unit_price: product.price,
                    qty:        1,
                    subtotal:   product.price,
                });
            }
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        updateQty(index, qty) {
            if (qty < 1) { this.removeItem(index); return; }
            this.items[index].qty = qty;
            this.items[index].subtotal = this.items[index].unit_price * qty;
        },

        submitCheckout() {
            if (this.items.length === 0) return;

            const container = document.getElementById('items-container');
            container.innerHTML = '';

            this.items.forEach((item, i) => {
                const fields = {
                    [`items[${i}][type]`]:  item.type,
                    [`items[${i}][id]`]:    item.id,
                    [`items[${i}][name]`]:  item.name,
                    [`items[${i}][price]`]: item.unit_price,
                    [`items[${i}][qty]`]:   item.qty,
                };
                Object.entries(fields).forEach(([name, value]) => {
                    const inp = document.createElement('input');
                    inp.type  = 'hidden';
                    inp.name  = name;
                    inp.value = value;
                    container.appendChild(inp);
                });
            });

            document.getElementById('checkout-form').submit();
        }
    };
}
</script>
</body>
</html>
