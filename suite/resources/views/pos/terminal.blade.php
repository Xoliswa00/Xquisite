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
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="font-sans antialiased bg-slate-950 text-slate-100 h-screen overflow-hidden">

<script>window.POS_TENANT_ID = {{ $tenantId }};</script>

<!-- Skeleton shown until Alpine hydrates; removed by x-init below -->
<div id="pos-skeleton" class="h-screen flex flex-col animate-pulse">
    <div class="h-14 bg-slate-900 border-b border-slate-800 shrink-0"></div>
    <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
        <div class="flex-1 p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 content-start">
            @for ($i = 0; $i < 8; $i++)
                <div class="bg-slate-800 rounded-xl h-24"></div>
            @endfor
        </div>
        <div class="md:w-96 md:shrink-0 h-[45vh] md:h-auto bg-slate-900"></div>
    </div>
</div>

<div x-data="pos()" x-cloak x-init="document.getElementById('pos-skeleton')?.remove(); init()" class="h-screen flex flex-col">

    <!-- Header bar -->
    <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white text-sm">← Back</a>
            <span class="text-white font-semibold">POS Terminal</span>
            @if($appointment)
                <span class="text-xs bg-[#001A3A]/50 text-[#B8D4F0] border border-[#002B5B] px-2 py-0.5 rounded-full">
                    Appointment #{{ $appointment->id }} · {{ $appointment->customer->name }}
                </span>
            @endif
            <template x-if="offlineQueue.length > 0">
                <span class="text-xs px-2 py-0.5 rounded-full border"
                      :class="offlineQueue.length >= 5 ? 'bg-red-900/50 text-red-300 border-red-800' : 'bg-amber-900/50 text-amber-300 border-amber-800'">
                    <span x-text="offlineQueue.length"></span> sale<span x-show="offlineQueue.length !== 1">s</span> saved offline — syncing…
                </span>
            </template>
            <template x-if="needsAttention.length > 0">
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-900/50 text-red-300 border border-red-800">
                    <span x-text="needsAttention.length"></span> need<span x-show="needsAttention.length === 1">s</span> attention
                </span>
            </template>
        </div>
        <a href="{{ route('pos.sales.index') }}" class="text-sm text-slate-400 hover:text-white">Sales History →</a>
    </header>

    <!-- Recovered cart banner -->
    <div x-show="showRecoveryBanner" x-cloak class="bg-[#001A3A]/60 border-b border-[#002B5B] px-6 py-2 flex items-center justify-between text-sm">
        <span class="text-[#B8D4F0]">Recovered an unsaved cart from <span x-text="recoveredAt"></span>.</span>
        <div class="flex gap-2">
            <button @click="restoreCart()" class="bg-[#0078D4] hover:bg-[#0068BD] text-white px-3 py-1 rounded-lg text-xs">Restore</button>
            <button @click="discardRecoveredCart()" class="text-slate-400 hover:text-white px-3 py-1 text-xs">Discard</button>
        </div>
    </div>

    <!-- Needs-attention panel -->
    <div x-show="needsAttention.length > 0" x-cloak class="bg-red-950/50 border-b border-red-900 px-6 py-2 space-y-1">
        <template x-for="(entry, index) in needsAttention" :key="entry.queuedAt">
            <div class="flex items-center justify-between text-xs">
                <span class="text-red-300">
                    Offline sale from <span x-text="new Date(entry.queuedAt).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})"></span>
                    failed to sync: <span x-text="entry.error"></span>
                </span>
                <button @click="dismissAttentionItem(index)" class="text-slate-400 hover:text-white shrink-0 ml-2">Dismiss</button>
            </div>
        </template>
    </div>

    <!-- Main split layout -->
    <div class="flex flex-col md:flex-row flex-1 overflow-hidden">

        <!-- LEFT: Product catalog -->
        <div class="flex-1 min-h-0 flex flex-col border-b md:border-b-0 md:border-r border-slate-800 overflow-hidden">

            <!-- Search -->
            <div class="p-4 border-b border-slate-800">
                <input type="text"
                       x-model="search"
                       placeholder="Search products…"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-4 py-2.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>

            <!-- Suggested products for this service -->
            @if($serviceSuggestions)
                <div class="px-4 py-3 border-b border-slate-800 bg-[#001A3A]/30">
                    <p class="text-xs text-[#0078D4] font-medium mb-2">
                        Suggested for {{ $appointment->services->pluck('name')->join(', ') }}
                    </p>
                    <div class="flex gap-2 overflow-x-auto">
                        <template x-for="p in suggestions" :key="p.id">
                            <button @click="addProduct(p)"
                                    class="shrink-0 bg-[#001A3A]/40 hover:bg-[#001A3A]/70 border border-[#002B5B] rounded-lg px-3 py-2 text-left transition-colors">
                                <p class="text-xs text-white font-medium" x-text="p.name"></p>
                                <p class="text-xs text-[#B8D4F0] font-bold mt-0.5" x-text="'R' + p.price.toFixed(2)"></p>
                            </button>
                        </template>
                    </div>
                </div>
            @endif

            <!-- Category tabs -->
            <div class="flex gap-2 px-4 py-3 border-b border-slate-800 overflow-x-auto">
                <button @click="activeCategory = ''"
                        :class="activeCategory === '' ? 'bg-[#0078D4] text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                        class="shrink-0 text-xs px-3 py-1.5 rounded-lg">
                    All
                </button>
                <template x-for="cat in categories" :key="cat">
                    <button @click="activeCategory = cat"
                            :class="activeCategory === cat ? 'bg-[#0078D4] text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
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
                                class="bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-[#0078D4] rounded-xl p-3 text-left transition-colors group">
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
                                                  :class="item.type === 'service' ? 'bg-[#001A3A]/50 text-[#B8D4F0]' : 'bg-slate-700 text-slate-300'"
                                                  x-text="item.type === 'service' ? 'Service' : 'Product'">
                                            </span>
                                        </div>
                                        <p class="text-sm font-medium text-white mt-1" x-text="item.name"></p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            R<span x-text="item.unit_price.toFixed(2)"></span> each
                                        </p>
                                    </div>
                                    <button @click="removeItem(index)"
                                            aria-label="Remove item"
                                            class="text-slate-600 hover:text-red-400 text-lg leading-none shrink-0 mt-1">×</button>
                                </div>

                                <!-- Quantity control -->
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-2">
                                        <button @click="updateQty(index, item.qty - 1)"
                                                aria-label="Decrease quantity"
                                                class="w-6 h-6 rounded bg-slate-700 hover:bg-slate-600 text-sm flex items-center justify-center">−</button>
                                        <span class="text-sm w-6 text-center" x-text="item.qty"></span>
                                        <button @click="updateQty(index, item.qty + 1)"
                                                aria-label="Increase quantity"
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
                           class="flex-1 bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
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
                                    :class="paymentMethod === method ? 'bg-[#0078D4] text-white border-[#0078D4]' : 'bg-slate-800 text-slate-400 border-slate-700'"
                                    class="border text-xs py-2 rounded-lg capitalize">
                                <span x-text="method.toUpperCase()"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Notes -->
                <textarea x-model="notes" rows="2"
                          placeholder="Notes (optional)"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none"></textarea>

                <!-- Checkout error -->
                <p x-show="checkoutError" x-cloak x-text="checkoutError" class="text-xs text-red-400"></p>

                <!-- Checkout button -->
                <button type="button"
                        @click="submitCheckout()"
                        :disabled="items.length === 0 || submitting"
                        :class="(items.length === 0 || submitting) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-500'"
                        class="w-full bg-emerald-600 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                    <span x-show="!submitting">Process Payment · R<span x-text="total.toFixed(2)"></span></span>
                    <span x-show="submitting" x-cloak>Processing…</span>
                </button>
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
        appointmentId: {{ $appointment?->id ?? 'null' }},
        customerId: {{ $appointment?->customer_id ?? 'null' }},

        tenantId: window.POS_TENANT_ID,
        idempotencyKey: null,
        submitting: false,
        checkoutError: null,
        offlineQueue: [],
        needsAttention: [],
        showRecoveryBanner: false,
        recoveredAt: null,
        _recovered: null,
        _syncing: false,
        _autosaveTimer: null,

        init() {
            this.idempotencyKey = crypto.randomUUID();
            this.loadQueueFromStorage();
            this.maybeOfferCartRecovery();
            this.watchCartForAutosave();

            window.addEventListener('online', () => this.trySyncQueue());
            navigator.serviceWorker?.addEventListener('message', (e) => {
                if (e.data?.type === 'pos-sync-trigger') this.trySyncQueue();
            });
            setInterval(() => {
                if (navigator.onLine && this.offlineQueue.length > 0) this.trySyncQueue();
            }, 20000);

            if (navigator.onLine && this.offlineQueue.length > 0) this.trySyncQueue();

            if ('serviceWorker' in navigator) {
                // Scoped to /pos only — sw.js lives at the root so it CAN
                // control the whole origin, but this app has no other
                // offline-capable pages, so keep its footprint to this one.
                navigator.serviceWorker.register('/sw.js', { scope: '/pos' }).catch(() => {});
            }
        },

        cartStorageKey() { return `pos_cart_${this.tenantId}`; },
        queueStorageKey() { return `pos_offline_queue_${this.tenantId}`; },
        attentionStorageKey() { return `pos_needs_attention_${this.tenantId}`; },

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

        // ── Cart autosave / recovery ──────────────────────────────────
        watchCartForAutosave() {
            this.$watch('items', () => this.scheduleAutosave());
            this.$watch('discount', () => this.scheduleAutosave());
            this.$watch('paymentMethod', () => this.scheduleAutosave());
            this.$watch('notes', () => this.scheduleAutosave());
        },

        scheduleAutosave() {
            clearTimeout(this._autosaveTimer);
            this._autosaveTimer = setTimeout(() => this.autosaveCart(), 400);
        },

        autosaveCart() {
            if (this.items.length === 0) {
                localStorage.removeItem(this.cartStorageKey());
                return;
            }
            try {
                localStorage.setItem(this.cartStorageKey(), JSON.stringify({
                    items: this.items,
                    discount: this.discount,
                    paymentMethod: this.paymentMethod,
                    notes: this.notes,
                    idempotencyKey: this.idempotencyKey,
                    savedAt: Date.now(),
                }));
            } catch (e) {
                // Storage full/unavailable — the sale stays safe in memory,
                // it just won't survive a refresh.
            }
        },

        maybeOfferCartRecovery() {
            if (this.items.length > 0) return; // preloaded from an appointment — don't override
            const raw = localStorage.getItem(this.cartStorageKey());
            if (!raw) return;
            try {
                const saved = JSON.parse(raw);
                if (saved.items?.length > 0) {
                    this._recovered = saved;
                    this.showRecoveryBanner = true;
                    this.recoveredAt = new Date(saved.savedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
            } catch (e) {}
        },

        restoreCart() {
            if (!this._recovered) return;
            this.items = this._recovered.items;
            this.discount = this._recovered.discount;
            this.paymentMethod = this._recovered.paymentMethod;
            this.notes = this._recovered.notes;
            this.idempotencyKey = this._recovered.idempotencyKey;
            this.showRecoveryBanner = false;
        },

        discardRecoveredCart() {
            localStorage.removeItem(this.cartStorageKey());
            this.showRecoveryBanner = false;
        },

        // ── Checkout ───────────────────────────────────────────────────
        buildPayload() {
            return {
                idempotency_key: this.idempotencyKey,
                items: this.items.map(i => ({ type: i.type, id: i.id, name: i.name, price: i.unit_price, qty: i.qty })),
                payment_method: this.paymentMethod,
                discount: this.discount,
                notes: this.notes,
                appointment_id: this.appointmentId,
                customer_id: this.customerId,
            };
        },

        async submitCheckout() {
            if (this.items.length === 0 || this.submitting) return;
            this.submitting = true;
            this.checkoutError = null;

            const payload = this.buildPayload();

            try {
                const result = await this.postCheckout(payload);
                this.onCheckoutSuccess(result);
            } catch (err) {
                if (err.offline) {
                    this.queueOffline(payload);
                } else {
                    this.checkoutError = err.message || 'Something went wrong. Please try again.';
                }
            } finally {
                this.submitting = false;
            }
        },

        async postCheckout(payload) {
            if (!navigator.onLine) {
                const e = new Error('offline'); e.offline = true; throw e;
            }

            let response;
            try {
                response = await fetch('{{ route('pos.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });
            } catch (networkErr) {
                const e = new Error('offline'); e.offline = true; throw e;
            }

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                // Session/CSRF expired mid-replay (redirected to login), or an
                // unexpected HTML error page — not something to silently retry.
                throw new Error('Your session may have expired. Please refresh and sign in again.');
            }

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Could not process this sale.');
            }

            return data;
        },

        onCheckoutSuccess(result) {
            this.items = [];
            this.discount = 0;
            this.notes = '';
            localStorage.removeItem(this.cartStorageKey());
            this.idempotencyKey = crypto.randomUUID();
            window.location.href = result.receipt_url;
        },

        // ── Offline queue / background sync ─────────────────────────────
        loadQueueFromStorage() {
            try {
                this.offlineQueue = JSON.parse(localStorage.getItem(this.queueStorageKey()) || '[]');
                this.needsAttention = JSON.parse(localStorage.getItem(this.attentionStorageKey()) || '[]');
            } catch (e) {
                this.offlineQueue = [];
                this.needsAttention = [];
            }
        },

        saveQueueToStorage() {
            localStorage.setItem(this.queueStorageKey(), JSON.stringify(this.offlineQueue));
            localStorage.setItem(this.attentionStorageKey(), JSON.stringify(this.needsAttention));
        },

        queueOffline(payload) {
            this.offlineQueue.push({ payload, queuedAt: Date.now() });
            this.saveQueueToStorage();
            this.requestBackgroundSync();

            // This sale is "done" from the cashier's perspective — clear the
            // active cart so they can start the next one.
            this.items = [];
            this.discount = 0;
            this.notes = '';
            localStorage.removeItem(this.cartStorageKey());
            this.idempotencyKey = crypto.randomUUID();
        },

        // Best-effort: not supported in Safari/iOS, which is why the online
        // listener + interval poll above are the primary sync triggers and
        // this is purely a bonus for browsers that support waking a closed
        // tab's worker to sync sooner.
        requestBackgroundSync() {
            navigator.serviceWorker?.ready
                .then((registration) => registration.sync?.register('pos-sync-offline-queue'))
                .catch(() => {});
        },

        async trySyncQueue() {
            if (this._syncing || this.offlineQueue.length === 0) return;
            this._syncing = true;

            const queue = [...this.offlineQueue];
            const stillQueued = [];

            for (let i = 0; i < queue.length; i++) {
                const entry = queue[i];
                try {
                    await this.postCheckout(entry.payload);
                    // success — dropped from the queue, nothing to do
                } catch (err) {
                    if (err.offline) {
                        // Network dropped again mid-sync — keep this and
                        // every remaining entry queued, try again later.
                        stillQueued.push(entry, ...queue.slice(i + 1));
                        break;
                    }
                    // A real server-side rejection (e.g. insufficient stock,
                    // since someone else may have sold the last unit while
                    // this sale was offline) — needs a human, not another
                    // silent retry.
                    this.needsAttention.push({ ...entry, error: err.message });
                }
            }

            this.offlineQueue = stillQueued;
            this.saveQueueToStorage();
            this._syncing = false;
        },

        dismissAttentionItem(index) {
            this.needsAttention.splice(index, 1);
            this.saveQueueToStorage();
        },
    };
}
</script>
</body>
</html>
