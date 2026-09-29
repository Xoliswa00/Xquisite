<x-app-layout>
    <x-slot name="header">Founding 20 Applications</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Founding 20 Applications</h2>
                <p class="text-slate-400 text-sm mt-1">Scored discovery questionnaire responses, highest score first</p>
            </div>
            <a href="{{ route('founding-twenty.show') }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">
                View public form
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Total applications</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">High-value candidates</p>
                <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['high'] }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Good candidates</p>
                <p class="text-2xl font-bold text-[#0078D4] mt-1">{{ $stats['good'] }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Selected</p>
                <p class="text-2xl font-bold text-[#D4AF37] mt-1">{{ $stats['selected'] }} <span class="text-sm text-slate-500 font-normal">/ 20</span></p>
            </div>
        </div>

        @if($applications->count() > 0)
            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Business</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Type</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Score</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Status</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Applied</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($applications as $application)
                                @php
                                    $tierColor = match($application->tier) {
                                        'high' => 'text-emerald-400',
                                        'good' => 'text-[#0078D4]',
                                        'potential' => 'text-amber-400',
                                        default => 'text-slate-400',
                                    };
                                    $statusColor = match($application->status) {
                                        'selected', 'converted' => 'bg-emerald-500/20 text-emerald-400',
                                        'reviewing' => 'bg-[#0078D4]/20 text-[#0078D4]',
                                        'waitlisted' => 'bg-amber-500/20 text-amber-400',
                                        'rejected' => 'bg-red-500/20 text-red-400',
                                        default => 'bg-slate-700 text-slate-300',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-white">{{ $application->business_name }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $application->owner_name }} · {{ $application->phone }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-slate-300 capitalize">{{ $application->business_type }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono font-semibold {{ $tierColor }}">{{ $application->score ?? '—' }}</span>
                                        <span class="text-xs text-slate-500">/ 100</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium capitalize {{ $statusColor }}">
                                            {{ $application->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-slate-400">{{ $application->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.founding-twenty.show', $application) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-slate-800 rounded-xl p-12 border border-slate-700 text-center">
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <h3 class="text-lg font-semibold text-slate-300">No applications yet</h3>
                <p class="text-slate-400 text-sm mt-1">Responses will appear here as the questionnaire link goes out.</p>
            </div>
        @endif
    </div>
</x-app-layout>
