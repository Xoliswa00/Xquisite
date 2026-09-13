<x-app-layout>
    <x-slot name="header">Outreach Campaigns</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Outreach Campaigns</h2>
                <p class="text-slate-400 text-sm mt-1">Plan an outreach wave before it starts, then watch it fill in by industry</p>
            </div>
            <a href="{{ route('admin.outreach-campaigns.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Campaign
            </a>
        </div>

        @if($campaigns->count() > 0)
            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Campaign</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Channel</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Target industry</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Progress</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Status</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($campaigns as $campaign)
                                @php
                                    $statusColor = match($campaign->status) {
                                        'active' => 'bg-emerald-500/20 text-emerald-400',
                                        'completed' => 'bg-slate-700 text-slate-300',
                                        default => 'bg-[#0078D4]/20 text-[#0078D4]',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-white">{{ $campaign->name }}</p>
                                        @if($campaign->started_at)
                                            <p class="text-xs text-slate-400 mt-0.5">Started {{ $campaign->started_at->format('d M Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-300">{{ $campaign->channel ?? '—' }}</td>
                                    <td class="px-6 py-4 text-slate-300 capitalize">{{ $campaign->target_business_type ?? 'Any' }}</td>
                                    <td class="px-6 py-4 text-slate-300">
                                        {{ $campaign->applications_count }}{{ $campaign->planned_count ? ' / ' . $campaign->planned_count : '' }} applied
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium capitalize {{ $statusColor }}">{{ $campaign->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.outreach-campaigns.show', $campaign) }}"
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
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <h3 class="text-lg font-semibold text-slate-300">No campaigns yet</h3>
                <p class="text-slate-400 text-sm mt-1 mb-4">Plan your next outreach wave before it starts.</p>
                <a href="{{ route('admin.outreach-campaigns.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg font-medium transition">
                    Create your first campaign
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
