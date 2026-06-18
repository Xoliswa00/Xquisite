<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Units &mdash; {{ $property->name }}</h2>
                <a href="{{ route('properties.show', $property) }}" class="text-sm text-slate-400 hover:text-white">&larr; Back to Property</a>
            </div>
            <a href="{{ route('properties.units.create', $property) }}"
               class="bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-4 py-2 rounded-lg">
                + Add Unit
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Unit #</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium">Beds / Baths</th>
                        <th class="px-4 py-3 font-medium">Size (sqm)</th>
                        <th class="px-4 py-3 font-medium">Monthly Rent</th>
                        <th class="px-4 py-3 font-medium">Deposit</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Renter</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($units as $unit)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 font-semibold text-slate-200">{{ $unit->unit_number }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ ucfirst($unit->type) }}</td>
                            <td class="px-4 py-3 text-slate-300">
                                {{ $unit->bedrooms ?? '—' }} / {{ $unit->bathrooms ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-300">
                                {{ $unit->size_sqm ? number_format($unit->size_sqm, 1) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-300">R{{ number_format($unit->monthly_rent, 2) }}</td>
                            <td class="px-4 py-3 text-slate-300">
                                {{ $unit->deposit_amount ? 'R'.number_format($unit->deposit_amount, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($unit->status === 'occupied') bg-emerald-900/40 text-emerald-400
                                    @elseif($unit->status === 'vacant') bg-yellow-900/40 text-yellow-400
                                    @else bg-orange-900/40 text-orange-400 @endif">
                                    {{ ucfirst($unit->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">
                                {{ $unit->activeLease?->renter?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('properties.units.show', [$property, $unit]) }}"
                                   class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-slate-500">
                                No units yet.
                                <a href="{{ route('properties.units.create', $property) }}" class="text-[#0078D4] hover:text-[#B8D4F0] ml-1">Add first unit</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
