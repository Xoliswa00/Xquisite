<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="space-y-6">

        {{-- Greeting --}}
        @php
            $hour = (int) now()->format('G');
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            $firstName = explode(' ', Auth::user()->name)[0];
        @endphp
        <div class="flex items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white" style="font-family:'Montserrat',sans-serif">{{ $greeting }}, {{ $firstName }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
            <a href="{{ route('appointments.create') }}"
               class="shrink-0 hidden sm:inline-flex items-center gap-1.5 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Booking
            </a>
        </div>

        {{-- Trial banner — only shown when on trial AND no modules have been activated yet --}}
        @if(Auth::user()->tenant?->isOnTrial())
            @php $daysLeft = (int) now()->diffInDays(Auth::user()->tenant->trial_ends_at, false); @endphp
            <div class="flex items-center justify-between px-5 py-3 rounded-xl bg-[#D4AF37]/10 border border-[#D4AF37]/30 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[#D4AF37]">
                        @if($daysLeft > 1)
                            Free trial — <strong>{{ $daysLeft }} days</strong> remaining. No billing until your trial ends.
                        @elseif($daysLeft === 1)
                            Free trial ends <strong>tomorrow</strong>. No billing today.
                        @else
                            Free trial ends <strong>today</strong>.
                        @endif
                    </span>
                </div>
                <a href="{{ route('settings.modules.index') }}" class="shrink-0 text-xs bg-[#D4AF37] hover:bg-[#C09B28] text-[#002B5B] font-semibold px-3 py-1.5 rounded-lg">Manage Modules</a>
            </div>
        @endif

        {{-- Onboarding checklist --}}
        @if(!$onboardingComplete)
            <div class="bg-slate-800 rounded-xl p-5 border border-[#0078D4]/20">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-[#D4AF37]">Get started with Xquisite</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Complete these steps to get your business running</p>
                    </div>
                    <span class="text-xs text-[#0078D4] font-medium">{{ collect($onboarding)->filter()->count() }}/{{ count($onboarding) }} done</span>
                </div>
                <div class="space-y-2">
                    @php
                        $steps = [
                            ['has_service',     'Add your first service',    'services.create'],
                            ['has_staff',       'Add a staff member',        'staff.create'],
                            ['has_appointment', 'Create your first booking', 'appointments.create'],
                        ];
                        if ($hasPos) {
                            $steps[] = ['has_product', 'Add a product to stock', 'products.create'];
                        }
                    @endphp
                    @foreach($steps as [$key, $label, $createRoute])
                        @php $done = $onboarding[$key]; @endphp
                        <div class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $done ? 'opacity-50' : 'bg-slate-700/40' }}">
                            <div class="w-5 h-5 rounded-full shrink-0 flex items-center justify-center {{ $done ? 'bg-emerald-600' : 'border-2 border-slate-600' }}">
                                @if($done)
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                            <span class="text-sm {{ $done ? 'line-through text-slate-500' : 'text-slate-300' }} flex-1">{{ $label }}</span>
                            @if(!$done)
                                <a href="{{ route($createRoute) }}" class="text-xs text-[#0078D4] hover:text-[#0065B8] shrink-0">Start →</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Revenue anchor — the number that matters most ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Collected this month — hero card --}}
            <div class="bg-slate-800 rounded-xl p-5 border border-emerald-800/40 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/20 to-transparent pointer-events-none"></div>
                <div class="relative">
                    <p class="text-xs text-slate-400 uppercase tracking-wide tracking-wider">Revenue this month</p>
                    <p class="stat-number text-4xl font-bold text-emerald-400 mt-2 leading-none">R{{ number_format($completedRevenue, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-2">{{ $completedCount }} completed {{ Str::plural('appointment', $completedCount) }}</p>
                </div>
            </div>
            {{-- Outstanding --}}
            <div class="{{ $awaitingCount > 0 ? 'bg-amber-900/25 border-amber-700/50' : 'bg-slate-800 border-slate-800' }} rounded-xl p-5 border">
                <p class="text-xs {{ $awaitingCount > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wide tracking-wider">Outstanding</p>
                <p class="stat-number text-4xl font-bold {{ $awaitingCount > 0 ? 'text-amber-300' : 'text-slate-600' }} mt-2 leading-none">R{{ number_format($awaitingTotal, 2) }}</p>
                <p class="text-xs text-slate-500 mt-2">
                    {{ $awaitingCount }} {{ Str::plural('appointment', $awaitingCount) }} awaiting payment
                    @if($awaitingCount > 0)
                        · <a href="{{ route('appointments.index', ['status' => 'awaiting_payment']) }}" class="text-amber-400 hover:text-amber-300">View →</a>
                    @endif
                </p>
            </div>
            {{-- Total billed --}}
            <div class="bg-slate-800 rounded-xl p-5 border border-slate-800">
                <p class="text-xs text-slate-400 uppercase tracking-wide tracking-wider">Total billed</p>
                <p class="stat-number text-4xl font-bold text-white mt-2 leading-none">R{{ number_format($completedRevenue + $awaitingTotal, 2) }}</p>
                <p class="text-xs text-slate-500 mt-2">
                    <span class="text-emerald-500">R{{ number_format($completedRevenue, 2) }}</span> collected
                    @if($awaitingTotal > 0)
                        · <span class="text-amber-400">R{{ number_format($awaitingTotal, 2) }}</span> pending
                    @endif
                </p>
            </div>
        </div>

        {{-- ── Operational stats ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="{{ route('appointments.index', ['date' => today()->toDateString()]) }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-colors group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Today</p>
                    <svg class="w-4 h-4 text-slate-600 group-hover:text-[#0078D4] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $todayCount }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ Str::plural('booking', $todayCount) }}</p>
            </a>
            <a href="{{ route('customers.index') }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-colors group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Customers</p>
                    <svg class="w-4 h-4 text-slate-600 group-hover:text-[#0078D4] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-3m-4 6H7a4 4 0 01-4-4v-2a4 4 0 014-4h1m4 0a4 4 0 100-8 4 4 0 000 8z"/></svg>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $totalCustomers }}</p>
                <p class="text-xs text-slate-500 mt-1">total</p>
            </a>
            <a href="{{ route('staff.index') }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-colors group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Staff</p>
                    <svg class="w-4 h-4 text-slate-600 group-hover:text-[#0078D4] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $activeStaff }}</p>
                <p class="text-xs text-slate-500 mt-1">active</p>
            </a>
            @if($hasPos)
            <a href="{{ route('stock.reorder-alerts') }}"
               class="{{ $reorderCount > 0 ? 'bg-amber-900/30 border border-amber-700/50' : 'bg-slate-800' }} hover:bg-slate-700/70 rounded-xl p-4 transition-colors group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs {{ $reorderCount > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wider">Reorder</p>
                    <svg class="w-4 h-4 {{ $reorderCount > 0 ? 'text-amber-500' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="stat-number text-3xl font-bold {{ $reorderCount > 0 ? 'text-amber-300' : 'text-slate-600' }}">{{ $reorderCount }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $reorderCount > 0 ? 'need restocking' : 'all stocked' }}</p>
            </a>
            @else
            <a href="{{ route('services.index') }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-colors group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Services</p>
                    <svg class="w-4 h-4 text-slate-600 group-hover:text-[#0078D4] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $activeServices }}</p>
                <p class="text-xs text-slate-500 mt-1">active</p>
            </a>
            @endif
            @can('manage-tenants')
                <a href="{{ route('admin.module-requests.index') }}"
                   class="{{ $pendingModuleRequests > 0 ? 'bg-[#0078D4]/10 border border-[#0078D4]/30' : 'bg-slate-800' }} hover:bg-slate-700/70 rounded-xl p-4 transition-colors group">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Requests</p>
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="stat-number text-3xl font-bold {{ $pendingModuleRequests > 0 ? 'text-[#0078D4]' : 'text-white' }}">{{ $pendingModuleRequests }}</p>
                    <p class="text-xs text-slate-500 mt-1">pending</p>
                </a>
            @endcan
        </div>

        <div class="grid lg:grid-cols-2 gap-6">

            <!-- Today's schedule -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Today's Schedule</h3>
                    <a href="{{ route('appointments.create') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8]">+ New</a>
                </div>
                @forelse($upcomingToday as $appt)
                    <div class="px-4 py-3 border-b border-slate-700/50 flex items-center gap-3">
                        <div class="text-xs text-slate-400 w-12 shrink-0">{{ $appt->scheduled_at->format('H:i') }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white font-medium truncate">{{ $appt->customer->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $appt->services->pluck('name')->join(', ') ?: '—' }} · {{ $appt->staff?->name ?? 'Unassigned' }}</p>
                        </div>
                        @php $colors = ['pending'=>'yellow','confirmed'=>'emerald']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800 shrink-0">
                            {{ ucfirst($appt->status) }}
                        </span>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-slate-500 text-sm">No bookings scheduled for today.</div>
                @endforelse
            </div>

            <!-- Recent activity -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Recent Bookings</h3>
                    <a href="{{ route('appointments.index') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8]">View all →</a>
                </div>
                @forelse($recentAppointments as $appt)
                    <a href="{{ route('appointments.show', $appt) }}"
                       class="px-4 py-3 border-b border-slate-700/50 flex items-center gap-3 hover:bg-slate-700/50 block">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white truncate">{{ $appt->customer->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $appt->services->pluck('name')->join(', ') ?: '—' }} · {{ $appt->scheduled_at->format('d M, H:i') }}</p>
                        </div>
                        @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800 shrink-0">
                            {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                        </span>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-slate-500 text-sm">
                        No bookings yet. <a href="{{ route('appointments.create') }}" class="text-[#0078D4] hover:text-[#0065B8]">Create one.</a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
