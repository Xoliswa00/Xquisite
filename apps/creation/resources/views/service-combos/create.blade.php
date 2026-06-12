<x-app-layout>
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- HEADER --}}
    <div class="border-b border-slate-200 pb-8">
        <nav class="flex mb-2">
            <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                <li><a href="{{ route('combos.index') }}" class="hover:text-slate-700">Service Combos</a></li>
                <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li class="text-slate-900">{{ isset($combo) ? 'Edit Combo' : 'New Combo' }}</li>
            </ol>
        </nav>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter">
            {{ isset($combo) ? 'Edit: ' . $combo->name : 'Create Service Combo' }}
        </h2>
        <p class="text-slate-400 text-sm mt-1">Select services, set a discount, and optionally limit the combo by date.</p>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-sm text-rose-600">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ isset($combo) ? route('combos.update', $combo) : route('combos.store') }}"
          x-data="comboBuilder()" x-init="init()">
        @csrf
        @if(isset($combo)) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT: MAIN FIELDS --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- NAME & DESCRIPTION --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Combo Details</h3>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Combo Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $combo->name ?? '') }}"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent"
                               placeholder="e.g. Full Day Spa Package" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 resize-none"
                                  placeholder="What's included in this combo...">{{ old('description', $combo->description ?? '') }}</textarea>
                    </div>
                </div>

                {{-- SERVICE SELECTOR --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Select Services <span class="text-rose-500">*</span></h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Min. 2 required</span>
                    </div>

                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        @forelse($services as $service)
                        @php
                            $servicePrice = (float) optional($service->prices->first())->price;
                            $checked = isset($combo) && $combo->services->contains($service->id);
                        @endphp
                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all"
                               :class="selectedIds.includes({{ $service->id }}) ? 'border-amber-400 bg-amber-50' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <div class="flex items-center gap-3">
                                <input type="checkbox"
                                       name="service_ids[]"
                                       value="{{ $service->id }}"
                                       {{ $checked ? 'checked' : '' }}
                                       x-model="selectedIds"
                                       @change="recalculate()"
                                       class="w-4 h-4 rounded accent-amber-500">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ $service->name }}</p>
                                    @if($service->description)
                                        <p class="text-xs text-slate-400 line-clamp-1">{{ $service->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="text-sm font-black text-slate-700 tabular-nums ml-4"
                                  data-price="{{ $servicePrice }}"
                                  data-id="{{ $service->id }}">
                                R{{ number_format($servicePrice, 2) }}
                            </span>
                        </label>
                        @empty
                        <p class="text-sm text-slate-400 text-center py-6">No services found. <a href="{{ route('products.create') }}" class="text-amber-600 font-bold">Create one first →</a></p>
                        @endforelse
                    </div>
                </div>

                {{-- DISCOUNT --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Discount</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Discount Type</label>
                            <select name="discount_type" x-model="discountType" @change="recalculate()"
                                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                                <option value="percentage" {{ old('discount_type', $combo->discount_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed"      {{ old('discount_type', $combo->discount_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount (R)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                <span x-text="discountType === 'percentage' ? 'Discount %' : 'Discount Amount (R)'"></span>
                            </label>
                            <input type="number" name="discount_value" step="0.01" min="0"
                                   x-model="discountValue" @input="recalculate()"
                                   value="{{ old('discount_value', $combo->discount_value ?? 0) }}"
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>
                    </div>

                    {{-- LIVE PRICING SUMMARY --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2" x-show="selectedIds.length > 0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Pricing Breakdown</p>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Total service price</span>
                            <span class="font-bold text-slate-700 tabular-nums">R<span x-text="totalPrice.toFixed(2)">0.00</span></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Discount</span>
                            <span class="font-bold text-rose-500 tabular-nums">− R<span x-text="discountAmount.toFixed(2)">0.00</span></span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex justify-between">
                            <span class="font-black text-slate-900 text-sm uppercase tracking-wide">Combo Price</span>
                            <span class="font-black text-lg text-slate-900 tabular-nums">R<span x-text="comboPrice.toFixed(2)">0.00</span></span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-emerald-600 font-bold">Customer saves</span>
                            <span class="font-black text-emerald-600 tabular-nums">R<span x-text="discountAmount.toFixed(2)">0.00</span></span>
                        </div>
                    </div>
                </div>

                {{-- TIME LIMITS --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Time Limit <span class="text-slate-300 font-normal normal-case text-xs">— optional</span></h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Valid From</label>
                            <input type="datetime-local" name="valid_from"
                                   value="{{ old('valid_from', isset($combo) && $combo->valid_from ? $combo->valid_from->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Valid Until</label>
                            <input type="datetime-local" name="valid_until"
                                   value="{{ old('valid_until', isset($combo) && $combo->valid_until ? $combo->valid_until->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Leave both empty for no time restriction. The combo will automatically deactivate after the end date.</p>
                </div>
            </div>

            {{-- RIGHT: SIDEBAR --}}
            <div class="space-y-6">

                {{-- STATUS --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Publish</h3>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $combo->is_active ?? true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded accent-slate-900">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Active</p>
                            <p class="text-xs text-slate-400">Visible and bookable</p>
                        </div>
                    </label>
                </div>

                {{-- SELECTED SUMMARY --}}
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5" x-show="selectedIds.length > 0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-3">Selected Services</p>
                    <template x-for="id in selectedIds" :key="id">
                        <div class="flex justify-between text-xs py-1 border-b border-amber-100 last:border-0">
                            <span class="text-slate-700 font-semibold" x-text="getServiceName(id)"></span>
                            <span class="text-slate-500 tabular-nums">R<span x-text="getServicePrice(id).toFixed(2)"></span></span>
                        </div>
                    </template>
                    <div class="mt-3 pt-2 border-t border-amber-200 flex justify-between">
                        <span class="text-xs font-black text-amber-700 uppercase tracking-wide">Combo Price</span>
                        <span class="text-sm font-black text-amber-700 tabular-nums">R<span x-text="comboPrice.toFixed(2)"></span></span>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="space-y-3">
                    <button type="submit"
                            class="w-full bg-slate-900 hover:bg-amber-400 hover:text-slate-900 text-white font-bold py-3 rounded-xl text-sm transition-all duration-300 shadow-lg shadow-slate-200">
                        {{ isset($combo) ? 'Update Combo' : 'Create Combo' }}
                    </button>
                    <a href="{{ route('combos.index') }}"
                       class="block w-full text-center bg-white border border-slate-200 text-slate-600 hover:text-slate-900 font-semibold py-3 rounded-xl text-sm transition-all">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function comboBuilder() {
    const servicePrices = @json($services->mapWithKeys(fn($s) => [$s->id => ['name' => $s->name, 'price' => (float) optional($s->prices->first())->price]]));
    const preSelected = @json(isset($combo) ? $combo->services->pluck('id')->toArray() : []);

    return {
        selectedIds: preSelected.map(Number),
        discountType: '{{ old('discount_type', $combo->discount_type ?? 'percentage') }}',
        discountValue: parseFloat('{{ old('discount_value', $combo->discount_value ?? 0) }}') || 0,
        totalPrice: 0,
        discountAmount: 0,
        comboPrice: 0,

        init() {
            this.recalculate();
        },

        recalculate() {
            this.totalPrice = this.selectedIds.reduce((sum, id) => {
                return sum + (servicePrices[id]?.price || 0);
            }, 0);

            if (this.discountType === 'percentage') {
                this.discountAmount = this.totalPrice * (this.discountValue / 100);
            } else {
                this.discountAmount = Math.min(parseFloat(this.discountValue) || 0, this.totalPrice);
            }

            this.comboPrice = Math.max(0, this.totalPrice - this.discountAmount);
        },

        getServiceName(id) {
            return servicePrices[id]?.name || '';
        },

        getServicePrice(id) {
            return servicePrices[id]?.price || 0;
        },
    };
}
</script>
</x-app-layout>
