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

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Service Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description', $service->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes) }}" min="5" max="2880" required
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <p class="text-xs text-slate-500 mt-0.5">For full-day events use 480+</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Pricing Type</label>
                        <select name="pricing_type" id="edit_pricing_type"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="flat"     {{ old('pricing_type', $service->pricing_type) === 'flat'     ? 'selected' : '' }}>Flat rate</option>
                            <option value="per_head" {{ old('pricing_type', $service->pricing_type) === 'per_head' ? 'selected' : '' }}>Per person / per head</option>
                            <option value="per_unit" {{ old('pricing_type', $service->pricing_type) === 'per_unit' ? 'selected' : '' }}>Per unit</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div id="edit-flat-price">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Flat Price (R)</label>
                        <input type="number" name="price" value="{{ old('price', $service->price) }}" min="0" step="0.01"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div id="edit-unit-price" style="display:none">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Price Per Person / Unit (R)</label>
                        <input type="number" name="price_per_unit" value="{{ old('price_per_unit', $service->price_per_unit) }}" min="0" step="0.01"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div id="edit-unit-label" style="display:none">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Unit Label</label>
                        <input type="text" name="unit_label" value="{{ old('unit_label', $service->unit_label) }}"
                               placeholder="per pax / per table"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
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
                           class="rounded bg-slate-700 border-slate-600 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-slate-300">Active (bookable)</label>
                </div>

                <!-- Product bundles -->
                <div class="border-t border-slate-700 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm font-medium text-slate-300">Product Bundle</p>
                            <p class="text-xs text-slate-500 mt-0.5">Products suggested at checkout when this service is booked</p>
                        </div>
                        <button type="button" @click="addRow()"
                                class="text-xs bg-indigo-700 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg">
                            + Add Product
                        </button>
                    </div>

                    <template x-if="rows.length === 0">
                        <p class="text-xs text-slate-600 py-2">No products linked yet.</p>
                    </template>

                    <div class="space-y-2">
                        <template x-for="(row, i) in rows" :key="i">
                            <div class="flex items-center gap-2">
                                <select :name="`bundles[${i}][product_id]`" x-model="row.product_id"
                                        class="flex-1 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    <option value="">— Select product —</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="p.name" :selected="row.product_id == p.id"></option>
                                    </template>
                                </select>
                                <input type="number" :name="`bundles[${i}][quantity]`" x-model.number="row.quantity"
                                       min="1" placeholder="Qty"
                                       class="w-20 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <button type="button" @click="removeRow(i)"
                                        class="text-slate-600 hover:text-red-400 text-xl leading-none">×</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">Save Changes</button>
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
        rows: existing.map(b => ({ product_id: b.product_id, quantity: b.quantity })),
        products: products,
        addRow() { this.rows.push({ product_id: '', quantity: 1 }); },
        removeRow(i) { this.rows.splice(i, 1); },
    };
}
</script>
</x-app-layout>
