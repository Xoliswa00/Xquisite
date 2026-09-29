<x-app-layout>
    <x-slot name="header">Variants — {{ $product->name }}</x-slot>

    <div class="max-w-3xl space-y-4">

        <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-white">
            ← Back to {{ $product->name }}
        </a>

        <!-- Option axes -->
        @php
            $initialRows = collect($product->variant_options ?: ['' => ['']])
                ->map(fn ($values, $name) => ['name' => $name, 'values' => is_array($values) ? implode(', ', $values) : ''])
                ->values();
        @endphp
        <div class="bg-slate-800 rounded-xl p-6" x-data="{
            rows: @js($initialRows),
            addRow() { if (this.rows.length < 4) this.rows.push({ name: '', values: '' }); },
            removeRow(i) { this.rows.splice(i, 1); },
        }">
            <h2 class="text-sm font-semibold text-slate-100 mb-1">Options</h2>
            <p class="text-xs text-slate-400 mb-4">
                e.g. <span class="text-slate-300">Size</span> = S, M, L, XL and <span class="text-slate-300">Color</span> = Red, Blue, Black.
                Saving generates a variant row for every combination — up to 4 options, existing variants and their stock are never touched.
            </p>

            <form method="POST" action="{{ route('products.variants.generate', $product) }}" class="space-y-3">
                @csrf

                <template x-for="(row, i) in rows" :key="i">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" :name="'options[' + i + '][name]'" x-model="row.name" placeholder="e.g. Size" required
                               class="sm:w-40 shrink-0 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <input type="text" :name="'options[' + i + '][values]'" x-model="row.values" placeholder="S, M, L, XL" required
                               class="flex-1 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <button type="button" @click="removeRow(i)" x-show="rows.length > 1"
                                class="shrink-0 text-slate-400 hover:text-red-400 text-sm px-2">Remove</button>
                    </div>
                </template>

                <div class="flex items-center justify-between pt-1">
                    <button type="button" @click="addRow()" x-show="rows.length < 4"
                            class="text-sm text-[#0078D4] hover:text-[#3396DD]">+ Add another option</button>
                    <button type="submit" class="bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-5 py-2 rounded-lg">
                        {{ $product->has_variants ? 'Save Options & Update Variants' : 'Save Options & Generate Variants' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing variants -->
        @if($product->variants->isNotEmpty())
            <div class="bg-slate-800 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-100 mb-4">{{ $product->variants->count() }} Variant(s)</h2>

                <form method="POST" action="{{ route('products.variants.update', $product) }}">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-3">
                        @foreach($product->variants as $i => $variant)
                            <div class="bg-slate-700/50 border border-slate-600 rounded-lg p-3">
                                <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant->id }}">

                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                    <p class="text-sm font-medium text-slate-100">{{ $variant->label }}</p>
                                    <form method="POST" action="{{ route('products.variants.destroy', [$product, $variant]) }}"
                                          onsubmit="return confirm('Remove this variant? Existing orders keep their own record of it.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-300">Remove</button>
                                    </form>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">SKU</label>
                                        <input type="text" name="variants[{{ $i }}][sku]" value="{{ old("variants.$i.sku", $variant->sku) }}"
                                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Price override</label>
                                        <input type="number" step="0.01" min="0" name="variants[{{ $i }}][price_override]"
                                               value="{{ old("variants.$i.price_override", $variant->price_override) }}"
                                               placeholder="R{{ number_format($product->price, 2) }}"
                                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Stock</label>
                                        <input type="number" min="0" name="variants[{{ $i }}][stock_quantity]"
                                               value="{{ old("variants.$i.stock_quantity", $variant->stock_quantity) }}" required
                                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                    </div>
                                    <div class="flex flex-col justify-end gap-1.5 pb-1">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="hidden" name="variants[{{ $i }}][track_stock]" value="0">
                                            <input type="checkbox" name="variants[{{ $i }}][track_stock]" value="1"
                                                   {{ old("variants.$i.track_stock", $variant->track_stock) ? 'checked' : '' }}
                                                   class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                                            Track stock
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="hidden" name="variants[{{ $i }}][is_active]" value="0">
                                            <input type="checkbox" name="variants[{{ $i }}][is_active]" value="1"
                                                   {{ old("variants.$i.is_active", $variant->is_active) ? 'checked' : '' }}
                                                   class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="mt-4 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-5 py-2 rounded-lg">
                        Save Variants
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
