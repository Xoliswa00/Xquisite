<x-app-layout>
    <x-slot name="header">Applicants</x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" action="{{ route('applicants.index') }}" class="flex gap-3 flex-1 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()"
                        class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    <option value="">All Statuses</option>
                    @foreach(['pending','approved','rejected','converted'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                @if(request('status'))
                    <a href="{{ route('applicants.index') }}"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Clear</a>
                @endif
            </form>
            <a href="{{ route('applicants.create') }}"
               class="w-full sm:w-auto text-center bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + Add Applicant
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Interested In</th>
                        <th class="px-4 py-3 font-medium">Income</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($applicants as $applicant)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 font-medium text-slate-200">{{ $applicant->name }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">
                                {{ $applicant->property?->name ?? '—' }}
                                @if($applicant->unit) — Unit {{ $applicant->unit->unit_number }} @endif
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $applicant->monthly_income ? 'R'.number_format($applicant->monthly_income, 2) : '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($applicant->status === 'approved') bg-emerald-900/40 text-emerald-400
                                    @elseif($applicant->status === 'rejected') bg-red-900/40 text-red-400
                                    @elseif($applicant->status === 'converted') bg-[#001A3A]/40 text-[#0078D4]
                                    @else bg-yellow-900/40 text-yellow-400 @endif">
                                    {{ ucfirst($applicant->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('applicants.show', $applicant) }}"
                                   class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                                No applicants found.
                                <a href="{{ route('applicants.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0] ml-1">Add first applicant</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $applicants->withQueryString()->links() }}

    </div>
</x-app-layout>
