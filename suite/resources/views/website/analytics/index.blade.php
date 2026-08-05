@php
    $chartW = 640;
    $chartH = 200;
    $maxCount = max(1, $series->max('count'));
    $points = $series->values()->map(function ($point, $i) use ($series, $chartW, $chartH, $maxCount) {
        $x = $series->count() > 1 ? ($i / ($series->count() - 1)) * $chartW : 0;
        $y = $chartH - ($point['count'] / $maxCount) * ($chartH - 20);
        return ['x' => round($x, 1), 'y' => round($y, 1), 'date' => $point['date'], 'count' => $point['count']];
    });
    $linePath = $points->map(fn ($p, $i) => ($i === 0 ? 'M' : 'L') . $p['x'] . ',' . $p['y'])->implode(' ');
    $areaPath = $linePath . " L{$chartW},{$chartH} L0,{$chartH} Z";
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('website.templates.index') }}" class="text-slate-400 hover:text-white transition-colors">← Website</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-lg font-semibold text-slate-100">Analytics</h2>
        </div>
    </x-slot>

    <div class="max-w-5xl space-y-6">

        {{-- Small live preview of the website these stats belong to --}}
        @if ($activeTemplate)
            <div class="rounded-xl border border-slate-800 bg-slate-800 p-4 flex items-center gap-4">
                <x-template-preview :template="$activeTemplate" ratio="aspect-video" class="w-28 shrink-0 border border-slate-700" />
                <div class="min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ $activeTemplate->name }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Traffic on <a href="{{ $tenant->website_url }}" target="_blank" rel="noopener" class="text-[#0078D4] hover:text-[#B8D4F0]">{{ $tenant->website_url }}</a> — updated in real time as visitors load your site.
                    </p>
                </div>
            </div>
        @else
            <p class="text-sm text-slate-400 max-w-2xl">
                Traffic on <a href="{{ $tenant->website_url }}" target="_blank" rel="noopener" class="text-[#0078D4] hover:text-[#B8D4F0]">{{ $tenant->website_url }}</a> — updated in real time as visitors load your site.
            </p>
        @endif

        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['label' => 'Visits Today', 'value' => $visitsToday],
                ['label' => 'Visits (30 days)', 'value' => $visits30],
                ['label' => 'Unique Visitors (30 days)', 'value' => $uniqueVisitors30],
                ['label' => 'Total Visits (all time)', 'value' => $totalVisits],
            ] as $tile)
                <div class="rounded-xl border border-slate-800 bg-slate-800 p-5">
                    <p class="text-2xl font-bold text-white">{{ number_format($tile['value']) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $tile['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Visits over time --}}
        <div class="rounded-xl border border-slate-800 bg-slate-800 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-100">Visits — last 30 days</h3>

            @if ($totalVisits === 0)
                <p class="mt-8 mb-4 text-center text-sm text-slate-500">
                    No visits recorded yet. Once your site goes live and people start visiting, activity will show up here.
                </p>
            @else
                <div class="relative mt-4" x-data="{
                    points: {!! \Illuminate\Support\Js::from($points) !!},
                    active: null,
                    chartW: {{ $chartW }},
                    chartH: {{ $chartH }},
                    onMove(e) {
                        const rect = e.currentTarget.getBoundingClientRect();
                        const ratio = (e.clientX - rect.left) / rect.width;
                        const idx = Math.max(0, Math.min(this.points.length - 1, Math.round(ratio * (this.points.length - 1))));
                        this.active = this.points[idx];
                    },
                }">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full h-48 overflow-visible" preserveAspectRatio="none"
                         @mousemove="onMove" @mouseleave="active = null">
                        {{-- Recessive gridlines --}}
                        @for ($i = 1; $i <= 3; $i++)
                            <line x1="0" x2="{{ $chartW }}" y1="{{ ($chartH - 20) / 4 * $i }}" y2="{{ ($chartH - 20) / 4 * $i }}" stroke="#334155" stroke-width="1" />
                        @endfor

                        <defs>
                            <linearGradient id="visitFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0078D4" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#0078D4" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        <path d="{{ $areaPath }}" fill="url(#visitFill)" />
                        <path d="{{ $linePath }}" fill="none" stroke="#0078D4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                        {{-- Crosshair --}}
                        <template x-if="active">
                            <g>
                                <line :x1="active.x" :x2="active.x" y1="0" :y2="chartH" stroke="#475569" stroke-width="1" stroke-dasharray="3,3" />
                                <circle :cx="active.x" :cy="active.y" r="4" fill="#0078D4" stroke="#0f172a" stroke-width="2" />
                            </g>
                        </template>
                    </svg>

                    {{-- Tooltip --}}
                    <div x-show="active" x-cloak
                         class="pointer-events-none absolute -translate-x-1/2 -translate-y-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-xs shadow-lg"
                         :style="`left: ${active ? (active.x / chartW) * 100 : 0}%; top: ${active ? (active.y / chartH) * 100 : 0}%; margin-top: -8px;`">
                        <p class="font-semibold text-white" x-text="active ? active.count + (active.count === 1 ? ' visit' : ' visits') : ''"></p>
                        <p class="text-slate-400" x-text="active ? active.date : ''"></p>
                    </div>

                    {{-- Axis labels: first / middle / last date --}}
                    <div class="mt-2 flex justify-between text-[10px] text-slate-500">
                        <span>{{ \Carbon\Carbon::parse($series->first()['date'])->format('d M') }}</span>
                        <span>{{ \Carbon\Carbon::parse($series->get((int) floor($series->count() / 2))['date'])->format('d M') }}</span>
                        <span>{{ \Carbon\Carbon::parse($series->last()['date'])->format('d M') }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Top referrers --}}
        <div class="rounded-xl border border-slate-800 bg-slate-800 p-5 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-100">Top Referrers — last 30 days</h3>
            @if ($topReferrers->isEmpty())
                <p class="mt-3 text-sm text-slate-500">No referrer data yet.</p>
            @else
                @php $maxRef = max(1, $topReferrers->max('count')); @endphp
                <div class="mt-4 space-y-3">
                    @foreach ($topReferrers as $ref)
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-300 truncate">{{ $ref['label'] }}</span>
                                <span class="text-slate-500 shrink-0 ml-2">{{ number_format($ref['count']) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-900 overflow-hidden">
                                <div class="h-full rounded-full bg-[#0078D4]" style="width: {{ max(4, ($ref['count'] / $maxRef) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
