<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Working Hours — {{ $staff->name }}</h2>
                <p class="text-sm text-slate-400 mt-0.5">Set which days and hours this staff member is available for bookings.</p>
            </div>
            <a href="{{ route('staff.show', $staff) }}" class="text-sm text-slate-400 hover:text-white">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                <ul class="space-y-1">
                    @foreach($errors->all() as $e) <li>• {{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Working hours form --}}
        <form method="POST" action="{{ route('staff.schedule.update', $staff) }}">
            @csrf
            @method('PUT')

            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-200">Weekly Schedule</h3>
                </div>

                <div class="divide-y divide-slate-700">
                    @php
                        $days = [0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
                    @endphp
                    @foreach($days as $num => $label)
                        @php $sched = $scheduleByDay[$num]; @endphp
                        <div x-data="{ on: {{ ($sched && $sched->is_active) ? 'true' : 'false' }} }"
                             class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4">

                            {{-- Toggle --}}
                            <label class="flex items-center gap-3 sm:w-36 cursor-pointer">
                                <input type="checkbox"
                                       name="days[]"
                                       value="{{ $num }}"
                                       x-model="on"
                                       {{ ($sched && $sched->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-indigo-500">
                                <span class="text-sm font-medium text-slate-200">{{ $label }}</span>
                            </label>

                            {{-- Time inputs — only enabled when day is on --}}
                            <div class="flex items-center gap-2" :class="!on && 'opacity-30 pointer-events-none'">
                                <input type="time"
                                       name="start_time[{{ $num }}]"
                                       value="{{ $sched?->start_time ?? '09:00' }}"
                                       class="bg-slate-700 border-slate-600 text-slate-200 rounded-lg text-sm px-3 py-1.5"
                                       :disabled="!on">
                                <span class="text-slate-500 text-sm">to</span>
                                <input type="time"
                                       name="end_time[{{ $num }}]"
                                       value="{{ $sched?->end_time ?? '17:00' }}"
                                       class="bg-slate-700 border-slate-600 text-slate-200 rounded-lg text-sm px-3 py-1.5"
                                       :disabled="!on">
                            </div>

                            @if($sched && $sched->is_active)
                                <span class="text-xs text-emerald-400 ml-auto">Working</span>
                            @elseif($sched)
                                <span class="text-xs text-slate-500 ml-auto">Off</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 border-t border-slate-700 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition">
                        Save Working Hours
                    </button>
                </div>
            </div>
        </form>

        {{-- Blocked times --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-200">Blocked Time</h3>
                <p class="text-xs text-slate-400">Holidays, training, personal days — no bookings during these periods.</p>
            </div>

            {{-- Add block form --}}
            <form method="POST" action="{{ route('staff.blocks.store', $staff) }}"
                  class="px-6 py-4 border-b border-slate-700">
                @csrf
                <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:items-end">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">From</label>
                        <input type="datetime-local" name="starts_at"
                               class="bg-slate-700 border-slate-600 text-slate-200 rounded-lg text-sm px-3 py-1.5"
                               value="{{ old('starts_at') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">To</label>
                        <input type="datetime-local" name="ends_at"
                               class="bg-slate-700 border-slate-600 text-slate-200 rounded-lg text-sm px-3 py-1.5"
                               value="{{ old('ends_at') }}">
                    </div>
                    <div class="flex-1 min-w-40">
                        <label class="block text-xs text-slate-400 mb-1">Reason (optional)</label>
                        <input type="text" name="reason" placeholder="e.g. Annual leave"
                               class="w-full bg-slate-700 border-slate-600 text-slate-200 rounded-lg text-sm px-3 py-1.5"
                               value="{{ old('reason') }}">
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white text-sm rounded-lg font-medium transition">
                        Add Block
                    </button>
                </div>
            </form>

            {{-- Existing blocks --}}
            @if($staff->blocks->count())
                <div class="divide-y divide-slate-700">
                    @foreach($staff->blocks as $block)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div>
                                <p class="text-sm text-slate-200">
                                    {{ $block->starts_at->format('d M Y H:i') }}
                                    &rarr;
                                    {{ $block->ends_at->format('d M Y H:i') }}
                                </p>
                                @if($block->reason)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $block->reason }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('staff.blocks.destroy', [$staff, $block]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-300">Remove</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="px-6 py-6 text-sm text-slate-500 text-center">No blocked periods set.</p>
            @endif
        </div>

    </div>
</x-app-layout>
