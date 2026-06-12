<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Leases</h2>
            <a href="{{ route('leases.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg">
                + New Lease
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('leases.index') }}" class="bg-slate-800 rounded-xl p-4">
            <div class="flex gap-3 flex-wrap">
                <select name="status" class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    <option value="">All Statuses</option>
                    @foreach(['pending','active','expired','terminated'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="property_id" class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    <option value="">All Properties</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" @selected(request('property_id') == $property->id)>{{ $property->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg">Filter</button>
                @if(request()->hasAny(['status','property_id']))
                    <a href="{{ route('leases.index') }}"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Property</th>
                        <th class="px-4 py-3 font-medium">Unit</th>
                        <th class="px-4 py-3 font-medium">Renter</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Start</th>
                        <th class="px-4 py-3 font-medium">End</th>
                        <th class="px-4 py-3 font-medium">Monthly Rent</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($leases as $lease)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-slate-200">{{ $lease->property?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $lease->unit?->unit_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $lease->renter?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($lease->status === 'active') bg-emerald-900/40 text-emerald-400
                                    @elseif($lease->status === 'pending') bg-yellow-900/40 text-yellow-400
                                    @elseif($lease->status === 'terminated') bg-red-900/40 text-red-400
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ ucfirst($lease->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ \Carbon\Carbon::parse($lease->start_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('d M Y') : '—' }}</td>
                            <td class="px-4 py-3 text-slate-300">R{{ number_format($lease->monthly_rent, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('leases.show', $lease) }}"
                                   class="text-indigo-400 hover:text-indigo-300 text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                No leases found.
                                <a href="{{ route('leases.create') }}" class="text-indigo-400 hover:text-indigo-300 ml-1">Create first lease</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $leases->withQueryString()->links() }}

    </div>
</x-app-layout>
