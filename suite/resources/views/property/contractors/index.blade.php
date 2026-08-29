<x-app-layout>
    <x-slot name="header">Contractors</x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        {{-- Search --}}
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('contractors.index') }}" class="flex gap-3 flex-1 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, company, email or phone..."
                       class="flex-1 bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 placeholder-slate-500">
                <button type="submit"
                        class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm rounded-lg">Search</button>
                @if(request('search'))
                    <a href="{{ route('contractors.index') }}"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Clear</a>
                @endif
            </form>
            <a href="{{ route('contractors.create') }}"
               class="w-full sm:w-auto text-center bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + Add Contractor
            </a>
        </div>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Trade</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Phone</th>
                        <th class="px-4 py-3 font-medium">Jobs</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($contractors as $contractor)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 font-medium text-slate-200">
                                {{ $contractor->name }}
                                @if($contractor->company_name)
                                    <span class="block text-xs text-slate-500">{{ $contractor->company_name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $contractor->trade ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $contractor->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $contractor->phone ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ ($contractor->maintenance_requests_count ?? 0) > 0 ? 'bg-[#0078D4]/20 text-[#0078D4]' : 'bg-slate-700 text-slate-400' }}">
                                    {{ $contractor->maintenance_requests_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $contractor->is_active ? 'bg-emerald-900/40 text-emerald-400' : 'bg-slate-700 text-slate-400' }}">
                                    {{ $contractor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('contractors.show', $contractor) }}"
                                   class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                No contractors found.
                                <a href="{{ route('contractors.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0] ml-1">Add first contractor</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $contractors->withQueryString()->links() }}

    </div>
</x-app-layout>
