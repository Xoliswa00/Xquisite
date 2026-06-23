<x-app-layout>
    <x-slot name="header">New Service</x-slot>

    <div class="flex flex-col lg:flex-row gap-6 items-start">

        {{-- ── Left: Service form ─────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0">
            <div class="bg-slate-800 rounded-xl p-6">
                <form method="POST" action="{{ route('services.store') }}" class="space-y-4"
                      x-data="bundleManager([], @js($products))">
                    @csrf
                    <x-form-errors />

                    {{-- Category picker --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Category</label>
                        @if($categories->isEmpty())
                            <p class="text-xs text-slate-500">No categories yet — create one in the panel →</p>
                            <input type="hidden" name="service_category_id" value="">
                        @else
                            <select name="service_category_id"
                                    class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                <option value="">— No category —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('service_category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->icon }} {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Service Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Duration (minutes) <span class="text-red-400">*</span></label>
                            <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="5" max="2880" required
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('duration_minutes') border-red-500 @enderror">
                            <p class="text-xs text-slate-500 mt-0.5">480 = 8 hrs</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Pricing Type</label>
                            <select name="pricing_type" id="pricing_type"
                                    class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                <option value="flat"     {{ old('pricing_type','flat') === 'flat'     ? 'selected' : '' }}>Flat rate</option>
                                <option value="per_head" {{ old('pricing_type') === 'per_head' ? 'selected' : '' }}>Per person</option>
                                <option value="per_unit" {{ old('pricing_type') === 'per_unit' ? 'selected' : '' }}>Per unit</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="price-fields">
                        <div id="flat-price-field">
                            <label class="block text-sm font-medium text-slate-300 mb-1">Flat Price (R)</label>
                            <input type="number" name="price" id="selling_price" value="{{ old('price', '0.00') }}" min="0" step="0.01"
                                   @input="sellingPrice = $event.target.valueAsNumber || 0"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                        <div id="unit-price-fields" style="display:none">
                            <label class="block text-sm font-medium text-slate-300 mb-1">Price Per Person / Unit (R)</label>
                            <input type="number" name="price_per_unit" value="{{ old('price_per_unit') }}" min="0" step="0.01"
                                   placeholder="e.g. 150.00"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                        <div id="unit-label-field" style="display:none">
                            <label class="block text-sm font-medium text-slate-300 mb-1">Unit Label</label>
                            <input type="text" name="unit_label" value="{{ old('unit_label') }}"
                                   placeholder="per pax / per table / per hour"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">
                                Cost Price (R)
                                <span class="text-xs font-normal text-slate-500 ml-1">your cost to deliver</span>
                            </label>
                            <input type="number" name="cost_price" id="cost_price_field" value="{{ old('cost_price') }}" min="0" step="0.01"
                                   @input="costPrice = $event.target.valueAsNumber || 0"
                                   placeholder="0.00"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            {{-- Margin indicator --}}
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
                            const sel = document.getElementById('pricing_type');
                            function toggle() {
                                const isFlat = sel.value === 'flat';
                                document.getElementById('flat-price-field').style.display = isFlat ? '' : 'none';
                                document.getElementById('unit-price-fields').style.display = isFlat ? 'none' : '';
                                document.getElementById('unit-label-field').style.display = isFlat ? 'none' : '';
                            }
                            sel.addEventListener('change', toggle);
                            toggle();
                        })();
                    </script>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                        <label for="is_active" class="text-sm text-slate-300">Active (bookable)</label>
                    </div>

                    {{-- Product bundles (inventory module only) --}}
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
                    </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                        <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">Create Service</button>
                        <a href="{{ route('services.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Right: Sidebar panels ──────────────────────────────────────── --}}
        <div class="w-full lg:w-72 lg:shrink-0 space-y-4">

            {{-- Existing services --}}
            <div class="bg-slate-800 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-200">Existing Services</h3>
                    <span class="text-xs text-slate-500">{{ $services->count() }} total</span>
                </div>

                @if($services->isEmpty())
                    <p class="text-xs text-slate-500">No services yet.</p>
                @else
                    @php
                        $grouped = $services->groupBy(fn($s) => $s->service_category_id ?? 0);
                        $catMap  = $categories->keyBy('id');
                    @endphp
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        @foreach($grouped as $catId => $group)
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                                    @if($catId && $catMap->has($catId))
                                        {{ $catMap[$catId]->icon }} {{ $catMap[$catId]->name }}
                                    @else
                                        Uncategorised
                                    @endif
                                </p>
                                <ul class="space-y-1">
                                    @foreach($group as $svc)
                                        <li class="flex items-center gap-2 py-1 border-b border-slate-700/50 last:border-0">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-slate-200 truncate">{{ $svc->name }}</p>
                                                <p class="text-[10px] text-slate-500">{{ $svc->duration_minutes }}min</p>
                                            </div>
                                            <span class="shrink-0 text-[10px] px-1.5 py-0.5 rounded {{ $svc->is_active ? 'bg-emerald-900/50 text-emerald-400' : 'bg-slate-700 text-slate-500' }}">
                                                {{ $svc->is_active ? 'Active' : 'Off' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Existing categories --}}
            <div class="bg-slate-800 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-200">Categories</h3>
                    <a href="{{ route('service-categories.index') }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0]">Manage all →</a>
                </div>

                @if($categories->isEmpty())
                    <p class="text-xs text-slate-500">No categories yet. Create one below.</p>
                @else
                    <ul class="space-y-1.5">
                        @foreach($categories as $cat)
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <span class="text-base leading-none">{{ $cat->icon }}</span>
                                <span class="flex-1 truncate">{{ $cat->name }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $cat->colorClass('bg') }} {{ $cat->colorClass('text') }}">
                                    {{ ucfirst($cat->color) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Quick create category --}}
            <div class="bg-slate-800 rounded-xl p-4" x-data="{ icon: '✨', color: 'indigo' }">
                <h3 class="text-sm font-semibold text-slate-200 mb-3">New Category</h3>

                @if(session('success') && str_contains(session('success'), 'Category created'))
                    <p class="text-xs text-green-400 mb-2">{{ session('success') }}</p>
                @endif

                <form method="POST" action="{{ route('service-categories.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_redirect" value="services.create">

                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg bg-slate-700 flex items-center justify-center text-xl shrink-0" x-text="icon"></div>
                        <input name="icon" x-model="icon" maxlength="10" placeholder="✨"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>

                    <input name="name" required placeholder="Category name" value="{{ old('name') }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-xs text-red-400">{{ $message }}</p>@enderror

                    {{-- Color grid --}}
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach(\App\Models\ServiceCategory::colorClasses() as $colorKey => $classes)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $colorKey }}" x-model="color" class="sr-only"
                                       {{ old('color', 'indigo') === $colorKey ? 'checked' : '' }}>
                                <div class="h-8 rounded-md {{ $classes['bg'] }} {{ $classes['border'] }} border-2 flex items-center justify-center transition-all"
                                     :class="color === '{{ $colorKey }}' ? 'ring-2 ring-white ring-offset-1 ring-offset-slate-800' : ''">
                                    <span class="text-[10px] font-medium {{ $classes['text'] }}">{{ ucfirst($colorKey) }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <input type="hidden" name="sort_order" value="0">
                    <input type="hidden" name="is_active" value="1">

                    <button type="submit"
                            class="w-full bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-medium py-2 rounded-lg transition-colors">
                        + Create Category
                    </button>
                </form>
            </div>

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
