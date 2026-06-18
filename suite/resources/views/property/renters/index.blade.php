<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Renters</h2>
            <a href="{{ route('renters.create') }}"
               class="bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-4 py-2 rounded-lg">
                + Add Renter
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Search --}}
        <form method="GET" action="{{ route('renters.index') }}" class="bg-slate-800 rounded-xl p-4">
            <div class="flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, email or phone..."
                       class="flex-1 bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 placeholder-slate-500">
                <button type="submit"
                        class="px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm rounded-lg">Search</button>
                @if(request('search'))
                    <a href="{{ route('renters.index') }}"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Clear</a>
                @endif
            </div>
        </form>

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
                            <td class="px-4 py-3 text-slate-400">{{ $renter->phone ?? '—' }}</td>
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
