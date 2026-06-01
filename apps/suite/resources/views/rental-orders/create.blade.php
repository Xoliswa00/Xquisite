<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rental-orders.index') }}" class="text-slate-400 hover:text-white">← Rentals</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-xl font-semibold">New Rental Order</h2>
        </div>
    </x-slot>

    <div class="max-w-xl py-8 px-4 sm:px-6 lg:px-8"
         x-data="{
             selectedProduct: null,
             products: @js($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'rental_rate'=>(float)$p->rental_rate,'total_units'=>$p->total_units])),
             qty: 1,
             total() { return this.selectedProduct ? +(this.selectedProduct.rental_rate * this.qty).toFixed(2) : 0; },
             setProduct(id) { this.selectedProduct = this.products.find(p => p.id == id) || null; }
         }">

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-lg text-sm">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('rental-orders.store') }}" class="bg-slate-800 rounded-xl p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Item <span class="text-red-400">*</span></label>
                <select name="product_id" required @change="setProduct($event.target.value)"
                        class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('product_id') border-red-500 @enderror">
                    <option value="">— Select item —</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>
                            {{ $p->name }} — R{{ number_format($p->rental_rate, 2) }}/event
                            @if($p->total_units) ({{ $p->total_units }} units total) @endif
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Customer</label>
                <select name="customer_id"
                        class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">— Walk-in / No customer —</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Quantity <span class="text-red-400">*</span></label>
                    <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                           x-model.number="qty"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('quantity') border-red-500 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Event Date <span class="text-red-400">*</span></label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('event_date') border-red-500 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Return By <span class="text-red-400">*</span></label>
                    <input type="date" name="return_due_at" value="{{ old('return_due_at') }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('return_due_at') border-red-500 @enderror">
                </div>
            </div>

            <div x-show="selectedProduct" class="bg-slate-700/50 rounded-lg px-4 py-3 text-sm">
                <span class="text-slate-400">Total charge:</span>
                <span class="text-white font-semibold ml-2" x-text="'R ' + total().toFixed(2)"></span>
                <span class="text-slate-500 text-xs ml-2" x-show="selectedProduct?.total_units">
                    · <span x-text="selectedProduct?.total_units"></span> units owned
                </span>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                <textarea name="notes" rows="2"
                          placeholder="Delivery details, special handling, colour/style notes…"
                          class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg font-medium">
                    Create Rental Order
                </button>
                <a href="{{ route('rental-orders.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
