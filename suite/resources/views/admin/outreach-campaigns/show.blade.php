@php
    $statusColor = match($campaign->status) {
        'active' => 'bg-emerald-500/20 text-emerald-400',
        'completed' => 'bg-slate-700 text-slate-300',
        default => 'bg-[#0078D4]/20 text-[#0078D4]',
    };
    $industryLabels = ['salon' => 'Salon', 'beauty' => 'Beauty', 'wellness' => 'Wellness/Spa', 'fitness' => 'Fitness', 'service' => 'Other service business', 'other' => 'Other'];
@endphp

<x-app-layout>
    <x-slot name="header">Outreach Campaign</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $campaign->name }}</h2>
                <p class="text-slate-400 text-sm mt-1">
                    {{ $campaign->channel ?? 'No channel set' }}
                    @if($campaign->target_business_type) &middot; targeting {{ $industryLabels[$campaign->target_business_type] ?? $campaign->target_business_type }} @endif
                </p>
            </div>
            <a href="{{ route('admin.outreach-campaigns.index') }}" class="text-sm text-slate-400 hover:text-slate-200">&larr; Back to list</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Status</p>
                <p class="mt-1"><span class="inline-flex px-2 py-1 rounded-full text-xs font-medium capitalize {{ $statusColor }}">{{ $campaign->status }}</span></p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Applied</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $campaign->applications->count() }}{{ $campaign->planned_count ? ' / ' . $campaign->planned_count : '' }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Selected</p>
                <p class="text-2xl font-bold text-[#0078D4] mt-1">{{ $campaign->applications->whereIn('status', ['selected', 'converted'])->count() }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Converted</p>
                <p class="text-2xl font-bold text-[#D4AF37] mt-1">{{ $campaign->applications->where('status', 'converted')->count() }}</p>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Progress by industry</h3>
            @if($breakdown->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700">
                                <th class="text-left py-2 pr-4 text-slate-400 font-medium">Industry</th>
                                <th class="text-left py-2 px-4 text-slate-400 font-medium">Applied</th>
                                <th class="text-left py-2 px-4 text-slate-400 font-medium">Selected</th>
                                <th class="text-left py-2 px-4 text-slate-400 font-medium">Converted</th>
                                <th class="text-left py-2 px-4 text-slate-400 font-medium">High-tier</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @foreach($breakdown as $industry => $stats)
                                <tr>
                                    <td class="py-2 pr-4 text-white capitalize">{{ $industryLabels[$industry] ?? $industry }}</td>
                                    <td class="py-2 px-4 text-slate-300">{{ $stats['applied'] }}</td>
                                    <td class="py-2 px-4 text-slate-300">{{ $stats['selected'] }}</td>
                                    <td class="py-2 px-4 text-slate-300">{{ $stats['converted'] }}</td>
                                    <td class="py-2 px-4 text-slate-300">{{ $stats['high_tier'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-400">No applications tagged to this campaign yet.</p>
            @endif
        </div>

        @if($campaign->notes)
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-2">Notes</h3>
                <p class="text-sm text-slate-400">{{ $campaign->notes }}</p>
            </div>
        @endif

        @if($campaign->applications->isNotEmpty())
            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-300">Applications</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Business</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Type</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Score</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Status</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($campaign->applications as $app)
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4 text-white">{{ $app->business_name }}</td>
                                    <td class="px-6 py-4 text-slate-300 capitalize">{{ $app->business_type }}</td>
                                    <td class="px-6 py-4 text-slate-300">{{ $app->score ?? '—' }}/100</td>
                                    <td class="px-6 py-4 text-slate-300 capitalize">{{ $app->status }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.founding-twenty.show', $app) }}" class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
