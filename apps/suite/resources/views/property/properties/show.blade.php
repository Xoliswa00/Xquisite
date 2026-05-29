<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $property->name }}</h2>
                <p class="text-sm text-slate-400 mt-0.5">{{ $property->address_line_1 }}, {{ $property->city }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('properties.units.create', $property) }}"
                   class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">+ Add Unit</a>
                <a href="{{ route('properties.edit', $property) }}"
                   class="px-3 py-2 bg-indigo-700 hover:bg-indigo-600 text-white text-sm rounded-lg">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-5 gap-3">
            @foreach([
                ['label'=>'Units',        'value'=>$stats['units'],        'color'=>'text-white'],
                ['label'=>'Occupied',     'value'=>$stats['occupied'],     'color'=>'text-emerald-400'],
                ['label'=>'Vacant',       'value'=>$stats['vacant'],       'color'=>'text-yellow-400'],
                ['label'=>'Maintenance',  'value'=>$stats['maintenance'],  'color'=>'text-orange-400'],
                ['label'=>'Monthly Rent', 'value'=>'R'.number_format($stats['monthly_rent'],2), 'color'=>'text-indigo-400'],
            ] as $kpi)
            <div class="bg-slate-800 rounded-xl p-4">
                <p class="text-xs text-slate-400 uppercase font-semibold">{{ $kpi['label'] }}</p>
                <p class="text-xl font-bold {{ $kpi['color'] }} mt-1">{{ $kpi['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Units --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-200">Units</h3>
                <a href="{{ route('properties.units.index', $property) }}" class="text-xs text-indigo-400 hover:text-indigo-300">Manage →</a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-2 font-medium">Unit</th>
                        <th class="px-4 py-2 font-medium">Type</th>
                        <th class="px-4 py-2 font-medium">Rent</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Renter</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($property->units as $unit)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 font-medium text-slate-200">{{ $unit->unit_number }}</td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ ucfirst($unit->type) }}</td>
                            <td class="px-4 py-2.5 text-slate-300">R{{ number_format($unit->monthly_rent, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($unit->status === 'occupied') bg-emerald-900/40 text-emerald-400
                                    @elseif($unit->status === 'vacant') bg-yellow-900/40 text-yellow-400
                                    @else bg-orange-900/40 text-orange-400 @endif">
                                    {{ ucfirst($unit->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">
                                {{ $unit->activeLease?->renter?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('properties.units.show', [$property, $unit]) }}" class="text-indigo-400 hover:text-indigo-300 text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No units yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
