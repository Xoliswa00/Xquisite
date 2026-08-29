<x-app-layout>
    <x-slot name="header">{{ $property->name }}</x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[#D4AF37]">{{ $property->name }}</h2>
                    <p class="text-sm text-slate-400 mt-0.5">{{ $property->address_line_1 }}, {{ $property->city }}</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('properties.units.create', $property) }}"
                       class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">+ Add Unit</a>
                    <a href="{{ route('properties.edit', $property) }}"
                       class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
                </div>
            </div>
            @if($property->tenant)
                @php $applyLink = route('apply.show', [$property->tenant->slug, $property]); @endphp
                <div class="flex items-center gap-2 bg-slate-900 rounded-lg px-3 py-2 mt-4" x-data="{ copied: false }">
                    <span class="text-xs text-slate-400 uppercase font-semibold shrink-0">Application Link</span>
                    <span class="text-xs text-slate-300 truncate flex-1">{{ $applyLink }}</span>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $applyLink }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            class="shrink-0 text-xs font-semibold px-2 py-1 rounded-md transition-all"
                            :class="copied ? 'bg-emerald-900/40 text-emerald-400' : 'bg-slate-700 text-slate-300 hover:bg-slate-600'">
                        <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-2">Share this link with a prospective applicant — they fill in their own details and upload their documents directly.</p>
            @endif
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-5 gap-3">
            @foreach([
                ['label'=>'Units',        'value'=>$stats['units'],        'color'=>'text-white'],
                ['label'=>'Occupied',     'value'=>$stats['occupied'],     'color'=>'text-emerald-400'],
                ['label'=>'Vacant',       'value'=>$stats['vacant'],       'color'=>'text-yellow-400'],
                ['label'=>'Maintenance',  'value'=>$stats['maintenance'],  'color'=>'text-orange-400'],
                ['label'=>'Monthly Rent', 'value'=>'R'.number_format($stats['monthly_rent'],2), 'color'=>'text-[#0078D4]'],
            ] as $kpi)
            <div class="bg-slate-800 rounded-xl p-4">
                <p class="text-xs text-slate-400 uppercase font-semibold">{{ $kpi['label'] }}</p>
                <p class="text-xl font-bold {{ $kpi['color'] }} mt-1">{{ $kpi['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Photos --}}
        <div class="bg-slate-800 rounded-xl p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-300">Photos</h3>

            @if($property->images->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($property->images as $image)
                        <div class="relative group">
                            <img src="{{ $image->url() }}" alt="{{ $property->name }}"
                                 class="w-full h-32 object-cover rounded-lg border border-slate-700">
                            <form method="POST" action="{{ route('properties.images.destroy', [$property, $image]) }}"
                                  class="absolute top-1.5 right-1.5"
                                  onsubmit="return confirm('Remove this photo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-900/80 text-slate-300 hover:bg-red-800/80 hover:text-white text-xs leading-none">
                                    &times;
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">No photos yet.</p>
            @endif

            <form method="POST" action="{{ route('properties.images.store', $property) }}"
                  enctype="multipart/form-data" class="pt-2 border-t border-slate-700 flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="photos[]" multiple accept="image/png,image/jpeg,image/webp" required
                       class="text-sm text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 file:text-sm hover:file:bg-slate-600">
                <button type="submit" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">
                    Add Photos
                </button>
            </form>
        </div>

        {{-- Units --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-200">Units</h3>
                <a href="{{ route('properties.units.index', $property) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0]">Manage →</a>
            </div>
            <table class="w-full text-sm summary-on-mobile">
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
                                    @else bg-yellow-900/40 text-yellow-400 @endif">
                                    {{ ucfirst($unit->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">
                                {{ $unit->activeLease?->renter?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('properties.units.show', [$property, $unit]) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
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
