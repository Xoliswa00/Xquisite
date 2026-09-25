<x-app-layout>
    <x-slot name="header">Founding 20 Funnel</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Founding 20 Funnel</h2>
                <p class="text-slate-400 text-sm mt-1">Where people drop out, which sources bring people who stay, and how far we are from the targets.</p>
            </div>
            <a href="{{ route('admin.founding-twenty.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">Back to applications</a>
        </div>

        {{-- Targets --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Against target</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach(['applications' => 'Finished applications', 'selected' => 'Selected', 'activated' => 'First win logged', 'paying' => 'Paying after free period'] as $key => $label)
                    @php $pct = $targets[$key] > 0 ? min(100, round($actuals[$key] / $targets[$key] * 100)) : 0; @endphp
                    <div>
                        <p class="text-sm text-slate-400">{{ $label }}</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ $actuals[$key] }} <span class="text-sm text-slate-500 font-normal">/ {{ $targets[$key] }}</span></p>
                        <div class="h-1.5 bg-slate-700 rounded-full mt-2"><div class="h-1.5 bg-[#0078D4] rounded-full" style="width: {{ $pct }}%"></div></div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-slate-500 mt-4">Targets live in config/founding_twenty.php and are placeholders until you confirm them.</p>
        </div>

        {{-- Stages --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Stage by stage</h3>
            @php $top = max(1, collect($stages)->first()); $prev = null; @endphp
            <div class="space-y-3">
                @foreach($stages as $label => $count)
                    <div>
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="text-slate-300">{{ $label }}</span>
                            <span class="text-white font-semibold">{{ $count }}
                                @if($prev !== null && $prev > 0)
                                    <span class="text-xs text-slate-500 font-normal">{{ round($count / $prev * 100) }}% of the step before</span>
                                @endif
                            </span>
                        </div>
                        <div class="h-2 bg-slate-700 rounded-full mt-1"><div class="h-2 bg-[#0078D4] rounded-full" style="width: {{ round($count / $top * 100) }}%"></div></div>
                    </div>
                    @php $prev = $count; @endphp
                @endforeach
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Drop-off --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-1">Where unfinished applicants stopped</h3>
                <p class="text-xs text-slate-400 mb-4">The furthest questionnaire section reached by people who gave their details but did not finish.</p>
                @if($dropOff->isEmpty())
                    <p class="text-sm text-slate-400">Nobody has dropped out yet.</p>
                @else
                    <dl class="space-y-2 text-sm">
                        @foreach($dropOff as $section => $count)
                            <div class="flex justify-between">
                                <dt class="text-slate-300">{{ $section === 0 ? 'Never got to the questions' : 'Stopped in section ' . $section . ' of 8' }}</dt>
                                <dd class="text-white font-semibold">{{ $count }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>

            {{-- Speed --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-1">How fast we answer</h3>
                <p class="text-xs text-slate-400 mb-4">We promise a reply within {{ config('founding_twenty.decision_within_days') }} days.</p>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-300">Average days to a decision</dt><dd class="text-white font-semibold">{{ $avgDaysToDecision ?? 'No decisions yet' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-300">Still waiting past our promise</dt><dd class="font-semibold {{ $overdue > 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $overdue }}</dd></div>
                </dl>
            </div>
        </div>

        {{-- Sources --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">By source</h3>
                <p class="text-xs text-slate-400 mt-1">Where applicants came from, and how many of them went the distance.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[28rem]">
                    <thead class="bg-slate-900/50 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-slate-300">Source</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-300">Started</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-300">Finished</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-300">Selected</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-300">Paying</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($bySource as $source => $row)
                            <tr>
                                <td class="px-6 py-3 text-white capitalize">{{ str_replace(['_', '-'], ' ', $source) }}</td>
                                <td class="px-6 py-3 text-right text-slate-300">{{ $row['started'] }}</td>
                                <td class="px-6 py-3 text-right text-slate-300">{{ $row['finished'] }}</td>
                                <td class="px-6 py-3 text-right text-slate-300">{{ $row['selected'] }}</td>
                                <td class="px-6 py-3 text-right text-slate-300">{{ $row['converted'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-6 text-center text-slate-400">No applications yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
