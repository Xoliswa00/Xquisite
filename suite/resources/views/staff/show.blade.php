<x-app-layout>
    <x-slot name="header">{{ $staff->name }}</x-slot>

    <div class="max-w-3xl space-y-4">

        <!-- Profile card -->
        <div class="bg-slate-800 rounded-xl p-5 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-0.5 min-w-0">
                    <h2 class="text-lg font-semibold text-[#D4AF37] truncate">{{ $staff->name }}</h2>
                    <p class="text-sm text-slate-400">{{ $staff->role ?? 'Staff member' }}</p>
                    @if($staff->email)
                        <p class="text-sm text-slate-400">{{ $staff->email }}</p>
                    @endif
                    @if($staff->phone)
                        <p class="text-sm text-slate-400">{{ $staff->phone }}</p>
                    @endif
                    @if($staff->services->count())
                        <div class="flex flex-wrap gap-1.5 pt-2">
                            @foreach($staff->services as $service)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-slate-700 text-slate-300 border border-slate-600">{{ $service->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if($staff->is_active)
                    <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                @else
                    <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('staff.schedule', $staff) }}"
                   class="flex-1 sm:flex-none text-center bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Schedule</a>
                <a href="{{ route('staff.edit', $staff) }}"
                   class="flex-1 sm:flex-none text-center bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Edit</a>
            </div>
        </div>

        <!-- Working hours summary -->
        @php
            $days = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
            $activeSchedules = $staff->schedules->where('is_active', true)->keyBy('day_of_week');
        @endphp
        @if($staff->schedules->count())
        <div class="bg-slate-800 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-slate-300">Working Hours</h3>
                <a href="{{ route('staff.schedule', $staff) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0]">Edit →</a>
            </div>
            <div class="flex gap-2 flex-wrap">
                @foreach($days as $num => $label)
                    @if($activeSchedules->has($num))
                        @php $s = $activeSchedules[$num]; @endphp
                        <div class="bg-slate-700 rounded-lg px-3 py-2 text-center">
                            <p class="text-xs font-semibold text-slate-300">{{ $label }}</p>
                            <p class="text-xs text-emerald-400 mt-0.5">{{ substr($s->start_time,0,5) }}–{{ substr($s->end_time,0,5) }}</p>
                        </div>
                    @else
                        <div class="bg-slate-900/40 rounded-lg px-3 py-2 text-center opacity-40">
                            <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">Off</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Active blocks -->
        @php $upcomingBlocks = $staff->blocks->filter(fn($b) => $b->ends_at > now()); @endphp
        @if($upcomingBlocks->count())
        <div class="bg-yellow-900/20 border border-yellow-800/50 rounded-xl p-4">
            <h3 class="text-sm font-medium text-yellow-400 mb-2">Upcoming Blocked Time</h3>
            <div class="space-y-1">
                @foreach($upcomingBlocks as $block)
                    <p class="text-xs text-slate-300">
                        {{ $block->starts_at->format('d M Y H:i') }} → {{ $block->ends_at->format('d M Y H:i') }}
                        @if($block->reason) <span class="text-slate-400">— {{ $block->reason }}</span> @endif
                    </p>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent appointments -->
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700">
                <h3 class="text-sm font-medium text-slate-300">Recent Appointments</h3>
            </div>

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700">
                @forelse($recentAppointments as $appt)
                    @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','awaiting_payment'=>'amber','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                    <a href="{{ route('appointments.show', $appt) }}" class="block px-4 py-3 hover:bg-slate-700/50 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm text-slate-300 font-medium">{{ $appt->scheduled_at->format('d M Y, H:i') }}</p>
                            <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                                {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $appt->services->pluck('name')->join(', ') ?: '—' }}</p>
                        @if($appt->customer)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $appt->customer->name }}</p>
                        @endif
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-slate-500 text-sm">No appointments yet.</div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Services</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($recentAppointments as $appt)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3 text-slate-300">{{ $appt->scheduled_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">
                                @if($appt->customer)
                                    <a href="{{ route('customers.show', $appt->customer) }}" class="text-[#0078D4] hover:text-[#B8D4F0]">{{ $appt->customer->name }}</a>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $appt->services->pluck('name')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','awaiting_payment'=>'amber','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No appointments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('staff.index') }}" class="inline-block text-sm text-slate-400 hover:text-white">← Back to staff</a>
    </div>
</x-app-layout>
