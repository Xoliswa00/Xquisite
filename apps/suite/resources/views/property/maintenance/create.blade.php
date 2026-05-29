<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">New Maintenance Request</h2>
            <a href="{{ route('maintenance.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6"
         x-data="{
             propertyId: '{{ old('property_id', $preUnit?->property_id ?? '') }}',
             unitId: '{{ old('unit_id', $preUnit?->id ?? '') }}',
             units: @js($preUnit ? [['id' => $preUnit->id, 'unit_number' => $preUnit->unit_number]] : []),
             loadingUnits: false,
             fetchUnits() {
                 if (!this.propertyId) { this.units = []; this.unitId = ''; return; }
                 this.loadingUnits = true;
                 fetch('/api/properties/' + this.propertyId + '/units')
                     .then(r => r.json())
                     .then(data => { this.units = data; this.loadingUnits = false; })
                     .catch(() => { this.units = []; this.loadingUnits = false; });
             }
         }">
        <form method="POST" action="{{ route('maintenance.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Location</h3>

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
                        <select name="unit_id" x-model="unitId" required
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
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Request Details</h3>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2"
                           placeholder="e.g. Leaking tap in bathroom">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Description *</label>
                    <textarea name="description" rows="4" required
                              class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2"
                              placeholder="Describe the issue in detail...">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Priority</label>
                        <select name="priority" class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            @foreach(['low','medium','high','urgent'] as $p)
                                <option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Assign To</label>
                        <input type="text" name="assigned_to" value="{{ old('assigned_to') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2"
                               placeholder="Name or team (optional)">
                    </div>
                </div>
            </div>

            <div class="bg-slate-700/50 rounded-xl p-4 text-xs text-slate-400 border border-slate-600">
                If the unit has an active lease, the maintenance request will be automatically linked to that lease and renter.
            </div>

            <div class="flex justify-between">
                <a href="{{ route('maintenance.index') }}"
                   class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-semibold">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
