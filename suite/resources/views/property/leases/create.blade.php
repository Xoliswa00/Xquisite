<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">New Lease</h2>
            <a href="{{ route('leases.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Back to Leases</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6"
         x-data="{
             propertyId: '{{ old('property_id', $preUnit?->property_id ?? '') }}',
             unitId: '{{ old('unit_id', $preUnit?->id ?? '') }}',
             units: @js($preUnit ? [['id' => $preUnit->id, 'unit_number' => $preUnit->unit_number, 'monthly_rent' => $preUnit->monthly_rent]] : []),
             monthlyRent: '{{ old('monthly_rent', $preUnit?->monthly_rent ?? '') }}',
             loadingUnits: false,
             fetchUnits() {
                 if (!this.propertyId) { this.units = []; this.unitId = ''; return; }
                 this.loadingUnits = true;
                 fetch('/api/properties/' + this.propertyId + '/units')
                     .then(r => r.json())
                     .then(data => { this.units = data; this.loadingUnits = false; })
                     .catch(() => { this.units = []; this.loadingUnits = false; });
             },
             onUnitChange() {
                 const unit = this.units.find(u => u.id == this.unitId);
                 if (unit) this.monthlyRent = unit.monthly_rent;
             }
         }">
        <form method="POST" action="{{ route('leases.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Property &amp; Unit</h3>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Property *</label>
                    <select name="property_id" x-model="propertyId" @change="fetchUnits()" required
                            class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        <option value="">Select property...</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" @selected(old('property_id', $preUnit?->property_id) == $property->id)>{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Unit *</label>
                    @if($preUnit)
                        <input type="hidden" name="unit_id" value="{{ $preUnit->id }}">
                        <div class="w-full bg-slate-700/50 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-2">
                            Unit {{ $preUnit->unit_number }} (pre-selected)
                        </div>
                    @else
                        <select name="unit_id" x-model="unitId" @change="onUnitChange()" required
                                class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2"
                                :disabled="loadingUnits || !propertyId">
                            <option value="">Select unit...</option>
                            <template x-for="unit in units" :key="unit.id">
                                <option :value="unit.id" x-text="'Unit ' + unit.unit_number"></option>
                            </template>
                        </select>
                        <p x-show="loadingUnits" class="text-xs text-slate-500 mt-1">Loading units...</p>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Renter *</label>
                    <select name="renter_id" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        <option value="">Select renter...</option>
                        @foreach($renters as $renter)
                            <option value="{{ $renter->id }}" @selected(old('renter_id') == $renter->id)>{{ $renter->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Lease Period</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Start Date *</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">End Date <span class="text-slate-500">(leave blank for month-to-month)</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Financials</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Monthly Rent (R) *</label>
                        <input type="number" name="monthly_rent" x-model="monthlyRent" step="0.01" min="0" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Deposit Amount (R)</label>
                        <input type="number" name="deposit_amount" value="{{ old('deposit_amount') }}" step="0.01" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="deposit_paid" value="1" @checked(old('deposit_paid')) class="rounded border-slate-600 bg-slate-700">
                        <span class="text-sm text-slate-300">Deposit already paid</span>
                    </label>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Notes</h3>
                <textarea name="notes" rows="3"
                          class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">{{ old('notes') }}</textarea>
            </div>

            <div class="bg-slate-700/50 rounded-xl p-4 text-xs text-slate-400 border border-slate-600">
                Activating a lease marks the unit as occupied and creates a billing subscription.
            </div>

            <div class="flex justify-between">
                <a href="{{ route('leases.index') }}"
                   class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-semibold">
                    Create Lease
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
