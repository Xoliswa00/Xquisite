<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Add Unit &mdash; {{ $property->name }}</h2>
            <a href="{{ route('properties.units.index', $property) }}" class="text-sm text-slate-400 hover:text-white">&larr; Back to Units</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('properties.units.store', $property) }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Unit Details</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Unit Number *</label>
                        <input type="text" name="unit_number" value="{{ old('unit_number') }}" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Type *</label>
                        <select name="type" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            @foreach(['apartment','studio','bachelor','townhouse','house','office','retail','warehouse','other'] as $t)
                                <option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Floor</label>
                    <input type="number" name="floor" value="{{ old('floor') }}" min="0"
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
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
                <h3 class="text-sm font-semibold text-slate-300">Financials</h3>

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
                        class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg text-sm font-semibold">
                    Save Unit
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
