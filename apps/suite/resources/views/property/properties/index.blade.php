<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Properties</h2>
            <a href="{{ route('properties.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg">
                + Add Property
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

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
                <div class="bg-slate-800 rounded-xl p-5 flex items-center justify-between hover:bg-slate-750 transition">
                    <div>
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
                    <div class="flex gap-2">
                        <a href="{{ route('properties.units.index', $property) }}"
                           class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs rounded-lg">
                            Units
                        </a>
                        <a href="{{ route('properties.show', $property) }}"
                           class="px-3 py-1.5 bg-indigo-700 hover:bg-indigo-600 text-white text-xs rounded-lg">
                            View
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-slate-800 rounded-xl p-12 text-center text-slate-500">
                    No properties yet.
                    <a href="{{ route('properties.create') }}" class="text-indigo-400 hover:text-indigo-300 ml-1">Add your first property</a>
                </div>
            @endforelse
        </div>

        {{ $properties->links() }}
    </div>
</x-app-layout>
