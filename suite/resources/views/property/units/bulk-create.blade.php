<x-app-layout>
    <x-slot name="header">Bulk Add Units &mdash; {{ $property->name }}</x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('properties.units.bulk-store', $property) }}" class="space-y-6"
              x-data="{ start: {{ old('start_number', '') ?: 'null' }}, count: {{ old('count', 1) }} }">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <p class="text-sm text-slate-400">
                Creates a run of identical units in one go &mdash; same type, size, and rent, numbered sequentially.
                For a floor with more than one layout (e.g. 10 two-bed units and 10 bachelors), submit this once per layout.
            </p>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Numbering</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Starting Unit Number *</label>
                        <input type="number" name="start_number" x-model.number="start" required min="0"
                               placeholder="e.g. 301"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">How Many *</label>
                        <input type="number" name="count" x-model.number="count" required min="1" max="100"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
                <p class="text-xs text-slate-500" x-show="start !== null && count > 0">
                    Will create <span x-text="count"></span> unit(s):
                    <span x-text="start"></span><template x-if="count > 1">
                        <span>&ndash;<span x-text="start + count - 1"></span></span>
                    </template>
                </p>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Type *</label>
                        <select name="type" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            @foreach(['apartment','studio','bachelor','townhouse','house','office','retail','warehouse','other'] as $t)
                                <option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Floor</label>
                        <input type="number" name="floor" value="{{ old('floor') }}" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Bedrooms</label>
                        <input type="number" name="bedrooms" value="{{ old('bedrooms') }}" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Bathrooms</label>
                        <input type="number" name="bathrooms" value="{{ old('bathrooms') }}" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Size (sqm)</label>
                    <input type="number" name="size_sqm" value="{{ old('size_sqm') }}" step="0.01" min="0"
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Financials (same for every unit in this batch)</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Monthly Rent (R) *</label>
                        <input type="number" name="monthly_rent" value="{{ old('monthly_rent') }}" step="0.01" min="0" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Deposit Amount (R)</label>
                        <input type="number" name="deposit_amount" value="{{ old('deposit_amount') }}" step="0.01" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Notes</h3>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('properties.units.index', $property) }}"
                   class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-semibold">
                    Create Units
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
