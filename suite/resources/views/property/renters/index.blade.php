<x-app-layout>
    <x-slot name="header">Renters</x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        {{-- Search --}}
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('renters.index') }}" class="flex gap-3 flex-1 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, email or phone..."
                       class="flex-1 bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 placeholder-slate-500">
                <button type="submit"
                        class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm rounded-lg">Search</button>
                @if(request('search'))
                    <a href="{{ route('renters.index') }}"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Clear</a>
                @endif
            </form>
            <a href="{{ route('renters.create') }}"
               class="w-full sm:w-auto text-center bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + Add Renter
            </a>
        </div>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Phone</th>
                        <th class="px-4 py-3 font-medium">Active Leases</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($renters as $renter)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 font-medium text-slate-200">{{ $renter->name }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $renter->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400">
                                @if($renter->phone)
                                    <x-whatsapp-link :phone="$renter->phone" :message="'Hi ' . $renter->name . ', this is ' . (Auth::user()->tenant?->name ?? config('app.name'))" />
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php $activeCount = $renter->leases_count ?? 0; @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $activeCount > 0 ? 'bg-emerald-900/40 text-emerald-400' : 'bg-slate-700 text-slate-400' }}">
                                    {{ $activeCount }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('renters.show', $renter) }}"
                                   class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                                No renters found.
                                <a href="{{ route('renters.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0] ml-1">Add first renter</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $renters->withQueryString()->links() }}

    </div>
</x-app-layout>
