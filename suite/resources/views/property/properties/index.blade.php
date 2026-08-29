<x-app-layout>
    <x-slot name="header">Properties</x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            @php $listingsLink = route('listings.index', Auth::user()->tenant->slug); @endphp
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-slate-800 rounded-lg px-3 py-2 flex-1 min-w-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xs text-slate-400 uppercase font-semibold shrink-0">Public Listings Page</span>
                    <span class="text-xs text-slate-300 truncate min-w-0 flex-1">{{ $listingsLink }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0" x-data="{ copied: false }">
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $listingsLink }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            class="shrink-0 text-xs font-semibold px-2 py-1 rounded-md transition-all"
                            :class="copied ? 'bg-emerald-900/40 text-emerald-400' : 'bg-slate-700 text-slate-300 hover:bg-slate-600'">
                        <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                    </button>
                    <a href="{{ $listingsLink }}" target="_blank" rel="noopener"
                       class="shrink-0 text-xs font-semibold px-2 py-1 rounded-md bg-[#0078D4] hover:bg-[#0065B8] text-white transition-colors">
                        View &rarr;
                    </a>
                </div>
            </div>
            <a href="{{ route('properties.create') }}"
               class="shrink-0 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-4 py-2 rounded-lg text-center">
                + Add Property
            </a>
        </div>

        {{-- KPI Strip --}}
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
            @foreach([
                ['label'=>'Properties',  'value'=>$stats['total_properties'], 'color'=>'text-white'],
                ['label'=>'Total Units', 'value'=>$stats['total_units'],      'color'=>'text-white'],
                ['label'=>'Occupied',    'value'=>$stats['occupied'],          'color'=>'text-emerald-400'],
                ['label'=>'Vacant',      'value'=>$stats['vacant'],            'color'=>'text-yellow-400'],
                ['label'=>'Overdue Rent','value'=>$stats['overdue_payments'],  'color'=>'text-red-400'],
                ['label'=>'Maintenance', 'value'=>$stats['open_maintenance'],  'color'=>'text-orange-400'],
            ] as $kpi)
            <div class="bg-slate-800 rounded-xl p-4">
                <p class="text-xs text-slate-400 uppercase font-semibold">{{ $kpi['label'] }}</p>
                <p class="text-2xl font-bold {{ $kpi['color'] }} mt-1">{{ $kpi['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Properties list --}}
        <div class="space-y-3">
            @forelse($properties as $property)
                <div class="bg-slate-800 rounded-xl p-5 flex items-center gap-4 justify-between hover:bg-slate-750 transition">
                    <div class="flex items-center gap-4 min-w-0">
                        @if($property->coverImage)
                            <img src="{{ $property->coverImage->url() }}" alt="{{ $property->name }}"
                                 class="w-14 h-14 object-cover rounded-lg border border-slate-700 shrink-0">
                        @else
                            <div class="w-14 h-14 rounded-lg border border-slate-700 bg-slate-900 flex items-center justify-center text-slate-600 text-xs shrink-0">
                                No photo
                            </div>
                        @endif
                        <div class="min-w-0">
                        <div class="flex items-center gap-3">
                            <p class="font-semibold text-white">{{ $property->name }}</p>
                            <span class="px-2 py-0.5 rounded text-xs bg-slate-700 text-slate-300">{{ ucfirst($property->type) }}</span>
                        </div>
                        <p class="text-sm text-slate-400 mt-0.5">{{ $property->address_line_1 }}, {{ $property->city }}</p>
                        <div class="flex gap-4 mt-2 text-xs text-slate-400">
                            <span><span class="text-white font-medium">{{ $property->units_count }}</span> units</span>
                            <span><span class="text-emerald-400 font-medium">{{ $property->occupied_units_count }}</span> occupied</span>
                            <span><span class="text-yellow-400 font-medium">{{ $property->vacant_units_count }}</span> vacant</span>
                        </div>
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('properties.units.index', $property) }}"
                           class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs rounded-lg">
                            Units
                        </a>
                        <a href="{{ route('properties.show', $property) }}"
                           class="px-3 py-1.5 bg-[#002B5B] hover:bg-[#0078D4] text-white text-xs rounded-lg">
                            View
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-slate-800 rounded-xl p-12 text-center text-slate-500">
                    No properties yet.
                    <a href="{{ route('properties.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0] ml-1">Add your first property</a>
                </div>
            @endforelse
        </div>

        {{ $properties->links() }}
    </div>
</x-app-layout>
