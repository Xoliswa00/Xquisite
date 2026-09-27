<x-app-layout>
    <x-slot name="header">Site Traffic</x-slot>

    @php
        $maxViews = max(1, $series->max('views'));
        $secs = fn ($s) => $s >= 60 ? floor($s / 60) . 'm ' . ($s % 60) . 's' : $s . 's';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Site traffic</h2>
                <p class="text-slate-400 text-sm mt-1">Who visits, which pages they read, and what they click. Counted on our own servers with no cookies and no personal data.</p>
            </div>
            <a href="{{ route('admin.traffic.heatmap') }}" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-medium transition">Click heat map</a>
        </div>

        <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-slate-400">Period</span>
                @foreach([7, 30, 90] as $d)
                    <a href="{{ route('admin.traffic.index', ['days' => $d, 'scope' => $scope]) }}" class="px-3 py-1.5 rounded-lg {{ $days === $d ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' }}">{{ $d }} days</a>
                @endforeach
            </div>
            <div class="flex items-center gap-2">
                <span class="text-slate-400">Showing</span>
                @foreach(['public' => 'Public website', 'app' => 'Inside the app', 'all' => 'Both'] as $key => $label)
                    <a href="{{ route('admin.traffic.index', ['days' => $days, 'scope' => $key]) }}" class="px-3 py-1.5 rounded-lg {{ $scope === $key ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Visitors</p>
                <p class="text-2xl font-bold text-white mt-1">{{ number_format($totals['visitors']) }}</p>
                <p class="text-xs text-slate-500 mt-1">Unique each day, added up</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Page views</p>
                <p class="text-2xl font-bold text-white mt-1">{{ number_format($totals['views']) }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Time on a page</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $totals['avg_seconds'] ? $secs($totals['avg_seconds']) : 'No data yet' }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">How far they scroll</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $totals['avg_scroll'] ? $totals['avg_scroll'] . '%' : 'No data yet' }}</p>
            </div>
        </div>

        {{-- Day by day --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Visits per day</h3>
            <div class="flex items-end gap-1 h-32" role="img" aria-label="Page views per day">
                @foreach($series as $day)
                    <div class="flex-1 min-w-0 flex flex-col justify-end h-full" title="{{ \Carbon\Carbon::parse($day['date'])->format('j M') }}: {{ $day['views'] }} views, {{ $day['visitors'] }} visitors">
                        <div class="bg-[#0078D4] rounded-t" style="height: {{ round($day['views'] / $maxViews * 100) }}%; min-height: {{ $day['views'] > 0 ? '2px' : '0' }}"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs text-slate-500 mt-2">
                <span>{{ \Carbon\Carbon::parse($series->first()['date'])->format('j M') }}</span>
                <span>{{ \Carbon\Carbon::parse($series->last()['date'])->format('j M') }}</span>
            </div>
        </div>

        {{-- Founding 20 --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
                <h3 class="text-sm font-semibold text-slate-300">Founding 20: is it getting attention?</h3>
                <a href="{{ route('admin.founding-twenty.funnel') }}" class="text-xs text-[#0078D4] hover:underline">Applications funnel</a>
            </div>
            <p class="text-sm text-slate-300">
                {{ number_format($founding20['visitors']) }} visitors read the programme page
                @if($founding20['change'] !== null)
                    <span class="{{ $founding20['change'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">({{ $founding20['change'] >= 0 ? 'up' : 'down' }} {{ abs($founding20['change']) }}% on the {{ $days }} days before)</span>
                @else
                    <span class="text-slate-500">(nothing to compare with yet)</span>
                @endif
            </p>
            <div class="mt-4 space-y-3">
                @php $top = max(1, $founding20['steps'][0]['visitors']); @endphp
                @foreach($founding20['steps'] as $step)
                    <div>
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="text-slate-300">{{ $step['label'] }} <span class="text-xs text-slate-500">{{ $step['path'] }}</span></span>
                            <span class="text-white font-semibold">{{ $step['visitors'] }}
                                @if($step['of_previous'] !== null)<span class="text-xs text-slate-500 font-normal">{{ $step['of_previous'] }}% of the step before</span>@endif
                            </span>
                        </div>
                        <div class="h-2 bg-slate-700 rounded-full mt-1"><div class="h-2 bg-[#0078D4] rounded-full" style="width: {{ round($step['visitors'] / $top * 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-slate-400 mt-4">
                {{ $founding20['cta_clicks'] }} clicks on "Start Your Application" &middot;
                {{ $founding20['started'] }} started an application &middot; {{ $founding20['submitted'] }} finished one
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Pages --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden lg:col-span-2">
                <div class="px-6 py-4 border-b border-slate-700"><h3 class="text-sm font-semibold text-slate-300">Most visited pages</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[32rem]">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Page</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Views</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Visitors</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Scroll</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse($topPages as $p)
                                <tr>
                                    <td class="px-6 py-3 text-white font-mono text-xs">{{ $p->path }}</td>
                                    <td class="px-6 py-3 text-right text-slate-300">{{ number_format($p->views) }}</td>
                                    <td class="px-6 py-3 text-right text-slate-300">{{ number_format($p->visitors) }}</td>
                                    <td class="px-6 py-3 text-right text-slate-300">{{ $p->scroll ? round($p->scroll) . '%' : '-' }}</td>
                                    <td class="px-6 py-3 text-right text-slate-300">{{ $p->seconds ? $secs(round($p->seconds)) : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">No visits recorded in this period yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sources --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-1">Where visitors come from</h3>
                <p class="text-xs text-slate-400 mb-4">Links with <span class="font-mono">?src=whatsapp</span> show as that source. Otherwise the website they came from, or Direct.</p>
                <dl class="space-y-2 text-sm">
                    @forelse($sources as $s)
                        <div class="flex justify-between gap-3"><dt class="text-slate-300 capitalize truncate">{{ $s->origin }}</dt><dd class="text-white font-semibold shrink-0">{{ number_format($s->views) }}</dd></div>
                    @empty
                        <p class="text-slate-400">Nothing yet.</p>
                    @endforelse
                </dl>
                @if($devices->isNotEmpty())
                    @php $deviceTotal = max(1, $devices->sum('views')); @endphp
                    <h3 class="text-sm font-semibold text-slate-300 mt-6 mb-2">Devices</h3>
                    <dl class="space-y-2 text-sm">
                        @foreach($devices as $d)
                            <div class="flex justify-between"><dt class="text-slate-300 capitalize">{{ $d->device }}</dt><dd class="text-white font-semibold">{{ round($d->views / $deviceTotal * 100) }}%</dd></div>
                        @endforeach
                    </dl>
                @endif
            </div>

            {{-- Clicks --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-1">Most clicked buttons and links</h3>
                <p class="text-xs text-slate-400 mb-4">What people actually press.</p>
                <dl class="space-y-3 text-sm">
                    @forelse($topClicks as $c)
                        <div class="flex justify-between gap-3">
                            <dt class="min-w-0">
                                <span class="text-slate-200 block truncate">{{ $c->label ?: ($c->href ?: 'Unlabelled') }}</span>
                                <span class="text-xs text-slate-500 block truncate">on {{ $c->path }}@if($c->href) &middot; goes to {{ $c->href }}@endif</span>
                            </dt>
                            <dd class="text-white font-semibold shrink-0">{{ number_format($c->clicks) }}</dd>
                        </div>
                    @empty
                        <p class="text-slate-400">No clicks recorded yet.</p>
                    @endforelse
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
