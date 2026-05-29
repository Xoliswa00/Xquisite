<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-semibold text-white">Calendar</h1>
                <div class="flex items-center gap-1">
                    <a href="{{ route('appointments.calendar', $prev) }}"
                       class="p-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <span class="text-sm text-slate-300 px-2">
                        {{ $days->first()->format('d M') }} – {{ $days->last()->format('d M Y') }}
                    </span>
                    <a href="{{ route('appointments.calendar', $next) }}"
                       class="p-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('appointments.calendar') }}"
                       class="ml-1 text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg">
                        Today
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($unassigned > 0)
                    <a href="{{ route('appointments.index', ['status' => 'unassigned']) }}"
                       class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-orange-900/40 border border-orange-700 text-orange-400 rounded-lg hover:bg-orange-900/60">
                        <span class="font-bold">{{ $unassigned }}</span> unassigned
                    </a>
                @endif
                <a href="{{ route('appointments.index') }}" class="text-xs text-slate-400 hover:text-white">List view</a>
                <a href="{{ route('appointments.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg">
                    + New Booking
                </a>
            </div>
        </div>
    </x-slot>

    <div class="overflow-x-auto">
        <div class="min-w-[900px]">

            {{-- Day headers --}}
            <div class="grid grid-cols-8 border-b border-slate-700 sticky top-0 bg-slate-900 z-10">
                <div class="py-3 px-2 text-xs text-slate-500 font-medium"></div>
                @foreach($days as $day)
                    <div class="py-3 px-2 text-center
                        {{ $day->isToday() ? 'bg-indigo-900/30' : '' }}">
                        <p class="text-xs font-medium text-slate-400 uppercase">{{ $day->format('D') }}</p>
                        <p class="text-lg font-bold {{ $day->isToday() ? 'text-indigo-400' : 'text-slate-200' }} mt-0.5">
                            {{ $day->format('j') }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Time grid --}}
            @foreach($hours as $hour)
                @php
                    $hourInt = (int) $hour;
                @endphp
                <div class="grid grid-cols-8 border-b border-slate-800 min-h-[56px]">

                    {{-- Hour label --}}
                    <div class="py-1 px-2 text-xs text-slate-600 font-medium text-right border-r border-slate-800 pt-1.5">
                        {{ $hour }}
                    </div>

                    {{-- Day columns --}}
                    @foreach($days as $day)
                        @php
                            $dateKey = $day->format('Y-m-d');
                            $slotAppts = ($appointments[$dateKey] ?? collect())->filter(function ($a) use ($hourInt) {
                                $h = (int) $a->scheduled_at->format('H');
                                return $h === $hourInt;
                            });
                        @endphp
                        <div class="border-r border-slate-800 p-0.5 {{ $day->isToday() ? 'bg-indigo-900/10' : '' }} relative group">
                            @foreach($slotAppts as $appt)
                                <a href="{{ route('appointments.show', $appt) }}"
                                   class="block rounded px-1.5 py-1 text-xs mb-0.5 truncate leading-tight
                                       @if($appt->isUnassigned()) bg-orange-900/60 border border-orange-700/50 text-orange-300
                                       @elseif($appt->status === 'confirmed') bg-emerald-900/60 border border-emerald-700/50 text-emerald-300
                                       @elseif($appt->status === 'completed') bg-blue-900/60 border border-blue-700/50 text-blue-300
                                       @else bg-slate-700/80 border border-slate-600/50 text-slate-300 @endif"
                                   title="{{ $appt->customer->name }} — {{ $appt->service->name }} {{ $appt->scheduled_at->format('H:i') }}">
                                    <span class="font-semibold">{{ $appt->scheduled_at->format('H:i') }}</span>
                                    {{ $appt->customer->name }}
                                    <span class="opacity-60">· {{ $appt->service->name }}</span>
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
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-orange-900/60 border border-orange-700/50"></span> Unassigned
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-slate-700/80 border border-slate-600/50"></span> Pending
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-emerald-900/60 border border-emerald-700/50"></span> Confirmed
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-blue-900/60 border border-blue-700/50"></span> Completed
        </span>
    </div>

</x-app-layout>
