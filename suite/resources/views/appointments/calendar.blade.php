@php
    $calJson = $appointments->map(fn($dayAppts) => $dayAppts->map(fn($a) => [
        'id'       => $a->id,
        'url'      => route('appointments.show', $a),
        'time'     => $a->scheduled_at->format('H:i'),
        'duration' => $a->duration_minutes ?? ($a->totalDuration() ?: 60),
        'customer' => $a->customer?->name ?? 'Customer',
        'staff'    => $a->staff?->name,
        'initials' => $a->staff ? strtoupper(implode('', array_map(fn($p) => $p[0], array_slice(explode(' ', $a->staff->name), 0, 2)))) : '?',
        'services' => $a->services->pluck('name')->join(', '),
        'status'   => $a->status,
    ])->values())->toJson();

    $todayStr       = now()->toDateString();
    $defaultDay     = $days->first(fn($d) => $d->isToday())?->toDateString() ?? $days->first()->toDateString();
    $savedView      = auth()->user()->preferences['calendar_view'] ?? 'grid';

    $dayMeta = $days->map(fn($d) => [
        'date'  => $d->toDateString(),
        'label' => $d->format('D'),
        'num'   => $d->format('j'),
        'count' => ($appointments[$d->toDateString()] ?? collect())->count(),
        'today' => $d->isToday(),
    ])->values()->toJson();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between" x-data>

            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-[#D4AF37]">Calendar</h1>

                {{-- Week nav --}}
                <div class="flex items-center gap-1">
                    <a href="{{ route('appointments.calendar', $prev) }}"
                       class="p-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <span class="text-sm text-slate-300 px-2 tabular-nums">
                        {{ $days->first()->format('d M') }} – {{ $days->last()->format('d M Y') }}
                    </span>
                    <a href="{{ route('appointments.calendar', $next) }}"
                       class="p-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('appointments.calendar') }}"
                       class="ml-1 text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg transition">Today</a>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($unassigned > 0)
                    <a href="{{ route('appointments.index', ['status' => 'unassigned']) }}"
                       class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-orange-900/40 border border-orange-700 text-orange-400 rounded-lg hover:bg-orange-900/60 transition">
                        <span class="font-bold">{{ $unassigned }}</span> unassigned
                    </a>
                @endif

                {{-- View toggle --}}
                <div class="flex items-center bg-slate-800 border border-slate-700 rounded-lg p-0.5"
                     x-data="{ v: $store.calView.mode }"
                     x-init="$watch('$store.calView.mode', val => v = val)">
                    <button @click="$store.calView.set('grid')"
                            :class="v === 'grid' ? 'bg-slate-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition"
                            title="Week grid">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                        </svg>
                        Week
                    </button>
                    <button @click="$store.calView.set('cards')"
                            :class="v === 'cards' ? 'bg-slate-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition"
                            title="Day cards">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        Day
                    </button>
                </div>

                <a href="{{ route('appointments.index') }}" class="text-xs text-slate-400 hover:text-white transition">List view</a>
                <a href="{{ route('appointments.create') }}"
                   class="bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-4 py-2 rounded-lg transition">
                    + New Booking
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ── Alpine store + both views ─────────────────────────────────────── --}}
    <div x-data="{
            selectedDay: '{{ $defaultDay }}',
            allAppts: {{ $calJson }},
            days: {{ $dayMeta }},
            get dayAppts()  { return this.allAppts[this.selectedDay] || []; },
            get dayLabel()  { const d = this.days.find(x => x.date === this.selectedDay); return d ? d.label + ' ' + d.num + ' {{ $days->first()->format('M Y') }}' : ''; },
            get dayCount()  { return this.dayAppts.length; },
            countFor(date)  { return (this.allAppts[date] || []).length; },
            statusPill(s)   {
                return {
                    confirmed: 'bg-emerald-900/40 border-emerald-700/50 text-emerald-300',
                    pending:   'bg-slate-700/60 border-slate-600/50 text-slate-300',
                    completed: 'bg-blue-900/40 border-blue-700/50 text-blue-300',
                    cancelled: 'bg-red-900/40 border-red-700/50 text-red-300',
                }[s] || 'bg-slate-700/60 border-slate-600/50 text-slate-300';
            },
            barColor(s)     {
                return { confirmed:'bg-emerald-500/40', pending:'bg-slate-600/40', completed:'bg-blue-500/40' }[s] || 'bg-slate-600/40';
            },
            initColor(i)    {
                const c = ['bg-[#0078D4]/20 text-[#0078D4]','bg-[#D4AF37]/20 text-[#D4AF37]','bg-emerald-500/20 text-emerald-400','bg-purple-500/20 text-purple-400'];
                return c[i % c.length];
            },
        }"
        x-init="$watch('$store.calView.mode', () => {})"
    >

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- GRID VIEW (existing weekly time-grid)                   --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div x-show="$store.calView.mode === 'grid'" x-cloak>

            <div class="overflow-x-auto">
                <div class="min-w-[900px]">

                    {{-- Day headers --}}
                    <div class="grid grid-cols-8 border-b border-slate-700 sticky top-0 bg-slate-900 z-10">
                        <div class="py-3 px-2 text-xs text-slate-500 font-medium"></div>
                        @foreach($days as $day)
                            <div class="py-3 px-2 text-center {{ $day->isToday() ? 'bg-[#001A3A]/30' : '' }}">
                                <p class="text-xs font-medium text-slate-400 uppercase">{{ $day->format('D') }}</p>
                                <p class="text-lg font-bold {{ $day->isToday() ? 'text-[#0078D4]' : 'text-slate-200' }} mt-0.5">{{ $day->format('j') }}</p>
                                @php $dc = ($appointments[$day->toDateString()] ?? collect())->count(); @endphp
                                @if($dc > 0)
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $dc }} booking{{ $dc > 1 ? 's' : '' }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Time rows --}}
                    @foreach($hours as $hour)
                    @php $hourInt = (int) $hour; @endphp
                    <div class="grid grid-cols-8 border-b border-slate-800 min-h-[56px]">
                        <div class="py-1 px-2 text-xs text-slate-600 font-medium text-right border-r border-slate-800 pt-1.5">{{ $hour }}</div>
                        @foreach($days as $day)
                            @php
                                $dateKey   = $day->toDateString();
                                $slotAppts = ($appointments[$dateKey] ?? collect())->filter(fn($a) => (int) $a->scheduled_at->format('H') === $hourInt);
                            @endphp
                            <div class="border-r border-slate-800 p-0.5 {{ $day->isToday() ? 'bg-[#001A3A]/10' : '' }}">
                                @foreach($slotAppts as $appt)
                                    <a href="{{ route('appointments.show', $appt) }}"
                                       class="block rounded px-1.5 py-1 text-xs mb-0.5 truncate leading-tight
                                              @if($appt->isUnassigned()) bg-orange-900/60 border border-orange-700/50 text-orange-300
                                              @elseif($appt->status === 'confirmed') bg-emerald-900/60 border border-emerald-700/50 text-emerald-300
                                              @elseif($appt->status === 'completed') bg-blue-900/60 border border-blue-700/50 text-blue-300
                                              @else bg-slate-700/80 border border-slate-600/50 text-slate-300 @endif"
                                       title="{{ $appt->customer?->name ?? 'Customer' }} — {{ $appt->services->pluck('name')->join(', ') }} {{ $appt->scheduled_at->format('H:i') }}">
                                        <span class="font-semibold">{{ $appt->scheduled_at->format('H:i') }}</span>
                                        {{ $appt->customer?->name ?? 'Customer' }}
                                        <span class="opacity-60">· {{ $appt->services->pluck('name')->join(', ') }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    @endforeach

                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-orange-900/60 border border-orange-700/50"></span>Unassigned</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-700/80 border border-slate-600/50"></span>Pending</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-900/60 border border-emerald-700/50"></span>Confirmed</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-900/60 border border-blue-700/50"></span>Completed</span>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- CARD VIEW (Creative Tim calendar-01 pattern)            --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div x-show="$store.calView.mode === 'cards'" x-cloak>

            {{-- Day strip --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-5">
                <div class="flex gap-2 justify-between">
                    <template x-for="d in days" :key="d.date">
                        <button @click="selectedDay = d.date"
                                :class="selectedDay === d.date
                                    ? 'bg-[#0078D4] border-[#0078D4]'
                                    : d.today
                                        ? 'border-[#0078D4]/40 text-[#0078D4]'
                                        : 'border-slate-700 hover:border-slate-500'"
                                class="flex flex-col items-center px-3 py-2.5 rounded-xl border transition flex-1 min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wide mb-1"
                                  :class="selectedDay === d.date ? 'text-blue-200' : 'text-slate-500'"
                                  x-text="d.label"></span>
                            <span class="text-base font-bold"
                                  :class="selectedDay === d.date ? 'text-white' : d.today ? 'text-[#0078D4]' : 'text-slate-300'"
                                  x-text="d.num"></span>
                            {{-- Dot indicator --}}
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full transition"
                                  :class="d.count > 0
                                    ? (selectedDay === d.date ? 'bg-white/60' : 'bg-[#0078D4]')
                                    : 'opacity-0 bg-transparent'"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Two-col layout: cards left, sidebar right --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Appointment cards --}}
                <div class="lg:col-span-2">

                    {{-- Day label --}}
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500"
                           x-text="dayLabel + ' · ' + dayCount + ' appointment' + (dayCount !== 1 ? 's' : '')"></p>
                        <a :href="'{{ route('appointments.create') }}?date=' + selectedDay"
                           class="text-xs text-[#0078D4] hover:underline">+ Book on this day</a>
                    </div>

                    {{-- No appointments --}}
                    <div x-show="dayAppts.length === 0"
                         class="flex flex-col items-center justify-center py-16 text-center bg-slate-900 border border-slate-800 rounded-xl">
                        <svg class="w-10 h-10 text-slate-700 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                        <p class="text-slate-500 text-sm">No bookings on this day</p>
                        <a :href="'{{ route('appointments.create') }}?date=' + selectedDay"
                           class="mt-3 text-xs text-[#0078D4] hover:underline">Add a booking →</a>
                    </div>

                    {{-- Appointment cards --}}
                    <div class="space-y-3">
                        <template x-for="(a, idx) in dayAppts" :key="a.id">
                            <a :href="a.url"
                               class="block bg-slate-900 border border-slate-800 rounded-xl p-4 hover:border-[#0078D4]/50 hover:shadow-lg hover:shadow-black/20 transition group">
                                <div class="flex items-start gap-3">
                                    {{-- Time --}}
                                    <div class="shrink-0 text-center w-12">
                                        <p class="text-sm font-bold text-white tabular-nums" x-text="a.time"></p>
                                        <p class="text-[10px] text-slate-500 mt-0.5" x-text="a.duration + ' min'"></p>
                                    </div>

                                    {{-- Coloured left bar --}}
                                    <div class="w-0.5 self-stretch rounded-full shrink-0"
                                         :class="{ 'bg-emerald-500/50': a.status === 'confirmed', 'bg-slate-600/50': a.status === 'pending', 'bg-blue-500/50': a.status === 'completed', 'bg-orange-500/50': a.status === 'unassigned' }"></div>

                                    {{-- Details --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                            <span class="font-semibold text-slate-100 text-sm group-hover:text-white transition" x-text="a.customer"></span>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border"
                                                  :class="statusPill(a.status)"
                                                  x-text="a.status.charAt(0).toUpperCase() + a.status.slice(1)"></span>
                                        </div>
                                        <p class="text-sm text-slate-400 truncate" x-text="a.services || 'No services'"></p>
                                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-1" x-show="a.staff">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                            <span x-text="a.staff"></span>
                                        </p>
                                        <p class="text-xs text-orange-400 mt-1" x-show="!a.staff">⚠ No staff assigned</p>
                                    </div>

                                    {{-- Staff initials avatar --}}
                                    <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-[10px] font-bold"
                                         :class="initColor(idx)"
                                         x-text="a.initials"></div>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>

                {{-- Sidebar: day stats + staff --}}
                <div class="space-y-4">

                    {{-- Day stats --}}
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-4">This day</p>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-400">Appointments</span>
                                <span class="text-sm font-bold text-white" x-text="dayCount"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-400">Total minutes</span>
                                <span class="text-sm font-bold text-white"
                                      x-text="dayAppts.reduce((s, a) => s + (a.duration || 0), 0) + ' min'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-400">Unassigned</span>
                                <span class="text-sm font-bold"
                                      :class="dayAppts.filter(a => !a.staff).length > 0 ? 'text-orange-400' : 'text-slate-500'"
                                      x-text="dayAppts.filter(a => !a.staff).length"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Staff on this day --}}
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-4">Staff on this day</p>
                        <div x-show="dayAppts.length === 0" class="text-sm text-slate-600">—</div>
                        <div class="space-y-3">
                            <template x-for="(member, mi) in [...new Map(dayAppts.filter(a => a.staff).map(a => [a.staff, a])).values()]" :key="member.staff">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0"
                                         :class="initColor(mi)"
                                         x-text="member.initials"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-200 font-medium truncate" x-text="member.staff"></p>
                                        <p class="text-[10px] text-slate-500"
                                           x-text="dayAppts.filter(a => a.staff === member.staff).length + ' booking(s)'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Quick book --}}
                    <a :href="'{{ route('appointments.create') }}?date=' + selectedDay"
                       class="flex items-center justify-center gap-2 w-full py-3 border-2 border-dashed border-slate-700 hover:border-[#0078D4] text-slate-500 hover:text-[#0078D4] rounded-xl text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Book on this day
                    </a>
                </div>

            </div>
        </div>

    </div>

    {{-- ── Alpine store for view preference ──────────────────────────────── --}}
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('calView', {
            mode: localStorage.getItem('xq-cal-view') || '{{ $savedView }}',
            prefUrl: '{{ route('user.preference') }}',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,

            set(v) {
                this.mode = v;
                localStorage.setItem('xq-cal-view', v);
                // Silently persist server-side for usage analytics
                fetch(this.prefUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ key: 'calendar_view', value: v }),
                }).catch(() => {}); // fire-and-forget, never blocks UI
            },
        });
    });
    </script>

</x-app-layout>
