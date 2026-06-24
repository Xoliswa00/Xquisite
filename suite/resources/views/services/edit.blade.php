<x-app-layout>
    <x-slot name="header">Edit Service</x-slot>

    <div class="max-w-xl space-y-4">
        <div class="bg-slate-800 rounded-xl p-6">
            @php
                $existingBundles = $service->serviceProducts->map(fn($sp) => [
                    'product_id' => $sp->product_id,
                    'quantity'   => $sp->quantity,
                ])->values()->all();
            @endphp

            <form method="POST" action="{{ route('services.update', $service) }}" class="space-y-4"
                  x-data="bundleManager(@js($existingBundles), @js($products))">
                @csrf
                @method('PATCH')

                <x-form-errors />

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Category</label>
                    <select name="service_category_id"
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('service_category_id') border-red-500 @enderror">
                        <option value="">— No category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('service_category_id', $service->service_category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->icon ? $cat->icon . ' ' : '' }}{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_category_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Service Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('description') border-red-500 @enderror">{{ old('description', $service->description) }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Duration (minutes) <span class="text-red-400">*</span></label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes) }}" min="5" max="2880" required
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('duration_minutes') border-red-500 @enderror">
                        @error('duration_minutes')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        <p class="text-xs text-slate-500 mt-0.5">For full-day events use 480+</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Pricing Type</label>
                        <select name="pricing_type" id="edit_pricing_type"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('pricing_type') border-red-500 @enderror">
                            <option value="flat"     {{ old('pricing_type', $service->pricing_type) === 'flat'     ? 'selected' : '' }}>Flat rate</option>
                            <option value="per_head" {{ old('pricing_type', $service->pricing_type) === 'per_head' ? 'selected' : '' }}>Per person / per head</option>
                            <option value="per_unit" {{ old('pricing_type', $service->pricing_type) === 'per_unit' ? 'selected' : '' }}>Per unit</option>
                        </select>
                        @error('pricing_type')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div id="edit-flat-price">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Flat Price (R)</label>
                        <input type="number" name="price" id="selling_price" value="{{ old('price', $service->price) }}" min="0" step="0.01"
                               @input="sellingPrice = $event.target.valueAsNumber || 0"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('price') border-red-500 @enderror">
                        @error('price')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div id="edit-unit-price" style="display:none">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Price Per Person / Unit (R)</label>
                        <input type="number" name="price_per_unit" value="{{ old('price_per_unit', $service->price_per_unit) }}" min="0" step="0.01"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('price_per_unit') border-red-500 @enderror">
                        @error('price_per_unit')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div id="edit-unit-label" style="display:none">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Unit Label</label>
                        <input type="text" name="unit_label" value="{{ old('unit_label', $service->unit_label) }}"
                               placeholder="per pax / per table"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('unit_label') border-red-500 @enderror">
                        @error('unit_label')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            Cost Price (R)
                            <span class="text-xs font-normal text-slate-500 ml-1">your cost to deliver</span>
                        </label>
                        <input type="number" name="cost_price" id="cost_price_field" value="{{ old('cost_price', $service->cost_price) }}" min="0" step="0.01"
                               @input="costPrice = $event.target.valueAsNumber || 0"
                               placeholder="0.00"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('cost_price') border-red-500 @enderror">
                        @error('cost_price')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs"
                           x-show="costPrice > 0 && sellingPrice > 0"
                           :class="sellingPrice >= costPrice ? 'text-emerald-400' : 'text-red-400'">
                            <span x-text="sellingPrice >= costPrice
                                ? 'Margin: R' + (sellingPrice - costPrice).toFixed(2) + ' (' + Math.round((sellingPrice - costPrice) / sellingPrice * 100) + '%)'
                                : 'Selling below cost by R' + (costPrice - sellingPrice).toFixed(2)">
                            </span>
                        </p>
                    </div>
                </div>

                <script>
                    (function() {
                        const sel = document.getElementById('edit_pricing_type');
                        function toggle() {
                            const isFlat = sel.value === 'flat';
                            document.getElementById('edit-flat-price').style.display  = isFlat ? '' : 'none';
                            document.getElementById('edit-unit-price').style.display  = isFlat ? 'none' : '';
                            document.getElementById('edit-unit-label').style.display  = isFlat ? 'none' : '';
                        }
                        sel.addEventListener('change', toggle);
                        toggle();
                    })();
                </script>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <label for="is_active" class="text-sm text-slate-300">Active (bookable)</label>
                </div>

                {{-- Materials / bundle (inventory module only) --}}
                @if($hasInventory)
                <div class="border-t border-slate-700 pt-4">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <p class="text-sm font-medium text-slate-300">Materials / Bundle</p>
                            <p class="text-xs text-slate-500 mt-0.5">Products used or included with this service</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="calcCostFromMaterials()"
                                    title="Fill cost price from materials"
                                    class="text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 px-3 py-1.5 rounded-lg">
                                ↻ Calc cost
                            </button>
                            <button type="button" @click="addRow()"
                                    class="text-xs bg-[#002B5B] hover:bg-[#0078D4] text-white px-3 py-1.5 rounded-lg">
                                + Add
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 mb-3">"Calc cost" sums unit cost × qty from your inventory and fills the Cost Price field.</p>

                    <template x-if="rows.length === 0">
                        <p class="text-xs text-slate-600 py-2">No materials linked yet.</p>
                    </template>

                    <div class="space-y-2">
                        <template x-for="(row, i) in rows" :key="i">
                            <div class="flex items-center gap-2">
                                <select :name="`bundles[${i}][product_id]`" x-model="row.product_id"
                                        @change="row.product_id = parseInt($event.target.value) || ''"
                                        class="flex-1 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                    <option value="">— Select product —</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="p.name" :selected="row.product_id == p.id"></option>
                                    </template>
                                </select>
                                <input type="number" :name="`bundles[${i}][quantity]`" x-model.number="row.quantity"
                                       min="1" placeholder="Qty"
                                       class="w-20 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                <button type="button" @click="removeRow(i)"
                                        class="text-slate-600 hover:text-red-400 text-xl leading-none">×</button>
                            </div>
                        </template>
                    </div>
                    @error('bundles')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                @endif

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-6 py-2 rounded-lg">Save Changes</button>
                    <a href="{{ route('services.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>

        <div class="bg-slate-800 rounded-xl p-4 border border-red-900/50">
            <p class="text-sm text-slate-400 mb-3">Delete this service. Appointments using it will need to be updated.</p>
            <form method="POST" action="{{ route('services.destroy', $service) }}"
                  onsubmit="return confirm('Delete {{ addslashes($service->name) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg">Delete Service</button>
            </form>
        </div>
    </div>

<script>
function bundleManager(existing, products) {
    return {
        rows: (existing || []).map(b => ({ product_id: b.product_id, quantity: b.quantity })),
        products: products,
        sellingPrice: parseFloat(document.getElementById('selling_price')?.value) || 0,
        costPrice: parseFloat(document.getElementById('cost_price_field')?.value) || 0,
        addRow() { this.rows.push({ product_id: '', quantity: 1 }); },
        removeRow(i) { this.rows.splice(i, 1); },
        calcCostFromMaterials() {
            let total = 0;
            for (const row of this.rows) {
                const product = this.products.find(p => p.id == row.product_id);
                if (product && product.cost_price) {
                    total += parseFloat(product.cost_price) * (row.quantity || 1);
                }
            }
            this.costPrice = Math.round(total * 100) / 100;
            const el = document.getElementById('cost_price_field');
            if (el) el.value = this.costPrice;
        },
    };
}
</script>
</x-app-layout>
