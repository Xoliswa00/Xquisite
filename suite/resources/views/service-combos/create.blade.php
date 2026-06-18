<x-app-layout>
    <x-slot name="header">{{ isset($combo) ? 'Edit Combo' : 'New Service Combo' }}</x-slot>

    <div class="max-w-5xl mx-auto" x-data="comboBuilder()">
        <form method="POST" action="{{ isset($combo) ? route('combos.update', $combo) : route('combos.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            @if(isset($combo)) @method('PUT') @endif
            <x-form-errors class="lg:col-span-3" />

            {{-- Main form --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
                    <h3 class="font-semibold text-[#D4AF37]">Combo Details</h3>

                    <div>
                        <x-input-label value="Name" />
                        <x-text-input name="name" class="mt-1 w-full" value="{{ old('name', $combo->name ?? '') }}" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label value="Description" />
                        <textarea name="description" rows="2" class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm focus:ring-[#0078D4] focus:border-[#0078D4]">{{ old('description', $combo->description ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Discount Type" />
                            <select name="discount_type" x-model="discountType" class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm">
                                <option value="percentage" {{ old('discount_type', $combo->discount_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type', $combo->discount_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed (R)</option>
                            </select>
                            <x-input-error :messages="$errors->get('discount_type')" />
                        </div>
                        <div>
                            <x-input-label value="Discount Value" />
                            <x-text-input name="discount_value" type="number" step="0.01" min="0" x-model.number="discountValue" class="mt-1 w-full" value="{{ old('discount_value', $combo->discount_value ?? 0) }}" required />
                            <x-input-error :messages="$errors->get('discount_value')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Valid From" />
                            <x-text-input name="valid_from" type="datetime-local" class="mt-1 w-full" value="{{ old('valid_from', isset($combo->valid_from) ? $combo->valid_from->format('Y-m-d\TH:i') : '') }}" />
                            <x-input-error :messages="$errors->get('valid_from')" />
                        </div>
                        <div>
                            <x-input-label value="Valid Until" />
                            <x-text-input name="valid_until" type="datetime-local" class="mt-1 w-full" value="{{ old('valid_until', isset($combo->valid_until) ? $combo->valid_until->format('Y-m-d\TH:i') : '') }}" />
                            <x-input-error :messages="$errors->get('valid_until')" />
                        </div>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $combo->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-[#0078D4]">
                        <span class="text-sm text-slate-300">Active</span>
                    </label>
                </div>

                {{-- Service picker --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="font-semibold text-[#D4AF37] mb-3">Select Services (min. 2)</h3>
                    @if($services->isEmpty())
                        <p class="text-sm text-slate-500 py-2">No active services yet. <a href="{{ route('services.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0]">Add a service first.</a></p>
                    @else
                        <div class="space-y-1 max-h-72 overflow-y-auto">
                            @foreach($services as $service)
                                @php $selectedIds = old('service_ids', isset($combo) ? $combo->services->pluck('id')->toArray() : []); @endphp
                                <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 cursor-pointer">
                                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                           class="rounded border-slate-600 bg-slate-800 text-[#0078D4]"
                                           {{ in_array($service->id, $selectedIds) ? 'checked' : '' }}
                                           @change="toggleService({{ $service->id }}, {{ (float) $service->price }}, {{ (float) ($service->cost_price ?? 0) }}, $event.target.checked)">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-200">{{ $service->name }}</p>
                                        @if($service->category)
                                            <p class="text-xs text-slate-500">{{ $service->category->icon }} {{ $service->category->name }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm text-slate-400 shrink-0">R{{ number_format($service->price, 2) }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <x-input-error :messages="$errors->get('service_ids')" class="mt-2" />
                </div>
            </div>

            {{-- Pricing sidebar --}}
            <div class="space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sticky top-6">
                    <h3 class="font-semibold text-[#D4AF37] mb-4">Live Pricing</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-400">
                            <span>Total (full price)</span>
                            <span>R<span x-text="totalPrice.toFixed(2)">0.00</span></span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Discount</span>
                            <span class="text-red-400">- R<span x-text="discountAmount.toFixed(2)">0.00</span></span>
                        </div>
                        <div class="flex justify-between font-bold text-white border-t border-slate-700 pt-3">
                            <span>Combo Price</span>
                            <span>R<span x-text="comboPrice.toFixed(2)">0.00</span></span>
                        </div>
                        <div class="flex justify-between text-emerald-400 text-xs">
                            <span>Customer saves</span>
                            <span>R<span x-text="savings.toFixed(2)">0.00</span></span>
                        </div>
                        <template x-if="totalCost > 0">
                            <div>
                                <div class="border-t border-slate-700 pt-3 mt-1 flex justify-between text-xs"
                                     :class="marginAmount >= 0 ? 'text-slate-400' : 'text-red-400'">
                                    <span>Your cost</span>
                                    <span>R<span x-text="totalCost.toFixed(2)"></span></span>
                                </div>
                                <div class="flex justify-between text-xs mt-1 font-medium"
                                     :class="marginAmount >= 0 ? 'text-emerald-400' : 'text-red-400'">
                                    <span x-text="marginAmount >= 0 ? 'Your margin' : 'Below cost!'"></span>
                                    <span x-text="(marginAmount >= 0 ? '+' : '') + 'R' + Math.abs(marginAmount).toFixed(2) + (totalCost > 0 ? ' (' + Math.abs(Math.round(marginAmount / comboPrice * 100)) + '%)' : '')"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="submit" class="mt-6 w-full py-2.5 bg-[#0078D4] hover:bg-[#0078D4] text-white font-medium rounded-lg text-sm transition-colors">
                        {{ isset($combo) ? 'Update Combo' : 'Create Combo' }}
                    </button>
                    <a href="{{ route('services.index', ['tab' => 'combos']) }}" class="block text-center text-xs text-slate-400 hover:text-white mt-3">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <script>
    function comboBuilder() {
        return {
            selectedServices: @json(isset($combo) ? $combo->services->map(fn($s) => ['id' => $s->id, 'price' => (float)$s->price, 'cost' => (float)($s->cost_price ?? 0)])->toArray() : []),
            discountType: '{{ old('discount_type', $combo->discount_type ?? 'percentage') }}',
            discountValue: {{ old('discount_value', $combo->discount_value ?? 0) }},
            get totalPrice() { return this.selectedServices.reduce((sum, s) => sum + s.price, 0); },
            get totalCost()  { return this.selectedServices.reduce((sum, s) => sum + s.cost,  0); },
            get discountAmount() {
                if (this.discountType === 'percentage') return this.totalPrice * (this.discountValue / 100);
                return Math.min(this.discountValue, this.totalPrice);
            },
            get comboPrice()    { return Math.max(0, this.totalPrice - this.discountAmount); },
            get savings()       { return this.totalPrice - this.comboPrice; },
            get marginAmount()  { return this.comboPrice - this.totalCost; },
            toggleService(id, price, cost, checked) {
                if (checked) {
                    if (!this.selectedServices.find(s => s.id === id)) {
                        this.selectedServices.push({ id, price, cost });
                    }
                } else {
                    this.selectedServices = this.selectedServices.filter(s => s.id !== id);
                }
            },
        };
    }
    </script>
</x-app-layout>
