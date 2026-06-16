<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="space-y-6">

        {{-- Trial banner — only shown when on trial AND no modules have been activated yet --}}
        @if(Auth::user()->tenant?->isOnTrial() && Auth::user()->tenant?->activeModules()->doesntExist())
            @php $daysLeft = (int) now()->diffInDays(Auth::user()->tenant->trial_ends_at, false); @endphp
            <div class="flex items-center justify-between px-5 py-3 rounded-xl bg-indigo-900/30 border border-indigo-700/50 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-indigo-300">
                        @if($daysLeft > 1)
                            Your free trial ends in <strong>{{ $daysLeft }} days</strong>.
                        @elseif($daysLeft === 1)
                            Your free trial ends <strong>tomorrow</strong>.
                        @else
                            Your free trial ends <strong>today</strong>.
                        @endif
                        Activate a module to get started.
                    </span>
                </div>
                <a href="{{ route('billing.index') }}" class="shrink-0 text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg font-medium">View Plans</a>
            </div>
        @endif

        {{-- Onboarding checklist --}}
        @if(!$onboardingComplete)
            <div class="bg-slate-800 rounded-xl p-5 border border-indigo-800/40">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Get started with Xquisite Suite</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Complete these steps to get your business running</p>
                    </div>
                    <span class="text-xs text-indigo-400 font-medium">{{ collect($onboarding)->filter()->count() }}/{{ count($onboarding) }} done</span>
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
                                <a href="{{ route($createRoute) }}" class="text-xs text-indigo-400 hover:text-indigo-300 shrink-0">Start →</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Stat cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Today's Bookings</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $todayCount }}</p>
                <a href="{{ route('appointments.index', ['date' => today()->toDateString()]) }}"
                   class="text-xs text-indigo-400 hover:text-indigo-300 mt-2 inline-block">View today →</a>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Customers</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $totalCustomers }}</p>
                <a href="{{ route('customers.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 mt-2 inline-block">Manage →</a>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Active Staff</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $activeStaff }}</p>
                <a href="{{ route('staff.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 mt-2 inline-block">Manage →</a>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Active Services</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $activeServices }}</p>
                <a href="{{ route('services.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 mt-2 inline-block">Manage →</a>
            </div>
            @if($hasPos)
            <div class="{{ $reorderCount > 0 ? 'bg-amber-900/30 border border-amber-700/50' : 'bg-slate-800' }} rounded-xl p-5">
                <p class="text-xs {{ $reorderCount > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wide">Reorder Alerts</p>
                <p class="text-3xl font-bold {{ $reorderCount > 0 ? 'text-amber-300' : 'text-slate-500' }} mt-1">{{ $reorderCount }}</p>
                <a href="{{ route('stock.reorder-alerts') }}"
                   class="text-xs {{ $reorderCount > 0 ? 'text-amber-400 hover:text-amber-300' : 'text-slate-500 hover:text-slate-400' }} mt-2 inline-block">
                    {{ $reorderCount > 0 ? 'View alerts →' : 'All stocked up' }}
                </a>
            </div>
            @endif
            @can('manage-tenants')
                <div class="bg-slate-800 rounded-xl p-5">
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Pending Module Requests</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $pendingModuleRequests }}</p>
                    <a href="{{ route('admin.module-requests.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 mt-2 inline-block">
                        Review requests →
                    </a>
                </div>
            @endcan
        </div>

        {{-- ── Money Movement ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Completed this month --}}
            <div class="bg-slate-800 rounded-xl p-5 border border-emerald-900/40">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Revenue this month</p>
                <p class="text-3xl font-bold text-emerald-400 mt-1">R{{ number_format($completedRevenue, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $completedCount }} completed {{ Str::plural('appointment', $completedCount) }}</p>
            </div>
            {{-- Awaiting payment --}}
            <div class="{{ $awaitingCount > 0 ? 'bg-amber-900/25 border-amber-700/50' : 'bg-slate-800 border-slate-700/30' }} rounded-xl p-5 border">
                <p class="text-xs {{ $awaitingCount > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wide">Outstanding</p>
                <p class="text-3xl font-bold {{ $awaitingCount > 0 ? 'text-amber-300' : 'text-slate-500' }} mt-1">R{{ number_format($awaitingTotal, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $awaitingCount }} {{ Str::plural('appointment', $awaitingCount) }} awaiting payment
                    @if($awaitingCount > 0)
                        · <a href="{{ route('appointments.index', ['status' => 'awaiting_payment']) }}" class="text-amber-400 hover:text-amber-300">View all →</a>
                    @endif
                </p>
            </div>
            {{-- Combined total --}}
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Total billed this month</p>
                <p class="text-3xl font-bold text-white mt-1">R{{ number_format($completedRevenue + $awaitingTotal, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    R{{ number_format($completedRevenue, 2) }} collected
                    @if($awaitingTotal > 0)
                        · <span class="text-amber-400">R{{ number_format($awaitingTotal, 2) }} pending</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">

            <!-- Today's schedule -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Today's Schedule</h3>
                    <a href="{{ route('appointments.create') }}" class="text-xs text-indigo-400 hover:text-indigo-300">+ New</a>
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
                    <a href="{{ route('appointments.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">View all →</a>
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
                        No bookings yet. <a href="{{ route('appointments.create') }}" class="text-indigo-400 hover:text-indigo-300">Create one.</a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
