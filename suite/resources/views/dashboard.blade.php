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

        {{-- Trial banner --}}
        @if(Auth::user()->tenant?->isOnTrial())
            @php $daysLeft = (int) now()->diffInDays(Auth::user()->tenant->trial_ends_at, false); @endphp
            <div class="flex items-center justify-between px-5 py-3 rounded-xl bg-[#D4AF37]/10 border border-[#D4AF37]/30 text-sm shadow-md shadow-[#D4AF37]/5">
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
            <div class="bg-slate-800 rounded-xl border border-[#0078D4]/20 shadow-lg shadow-[#0078D4]/8 overflow-hidden">
                <div class="px-5 py-4 border-b border-[#0078D4]/15 bg-gradient-to-r from-[#0078D4]/10 to-transparent flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-[#D4AF37]">Get started with Xquisite</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Complete these steps to get your business running</p>
                    </div>
                    <span class="text-xs text-[#0078D4] font-semibold bg-[#0078D4]/10 px-2.5 py-1 rounded-full border border-[#0078D4]/20">{{ collect($onboarding)->filter()->count() }}/{{ count($onboarding) }} done</span>
                </div>
                <div class="p-5 space-y-2">
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

        {{-- Profile completeness --}}
        @if(!$profileComplete)
            <div class="bg-slate-800 rounded-xl border border-amber-700/30 shadow-lg shadow-amber-950/20 overflow-hidden">
                <div class="px-5 py-4 border-b border-amber-700/20 bg-gradient-to-r from-amber-500/8 to-transparent flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-500/25 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-amber-300">Complete your profile</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Clients and payment flows need this information</p>
                        </div>
                    </div>
                    <span class="text-xs text-amber-400 font-semibold bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20 shrink-0">
                        {{ collect($profileChecks)->filter(fn($c) => $c['filled'])->count() }}/{{ count($profileChecks) }} done
                    </span>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @php
                        $profileAnchors = ['phone' => 'business-details', 'logo' => 'logo', 'address' => 'business-details', 'banking' => 'banking'];
                    @endphp
                    @foreach($profileChecks as $check)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $check['filled'] ? 'opacity-45' : 'bg-slate-700/40' }}">
                            <div class="w-5 h-5 rounded-full shrink-0 flex items-center justify-center {{ $check['filled'] ? 'bg-emerald-600' : 'border-2 border-amber-700/60' }}">
                                @if($check['filled'])
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                            <span class="text-sm {{ $check['filled'] ? 'line-through text-slate-500' : 'text-slate-300' }} flex-1">{{ $check['label'] }}</span>
                            @if(!$check['filled'])
                                <a href="{{ route('profile.edit') }}#{{ $profileAnchors[$check['key']] }}" class="text-xs text-amber-400 hover:text-amber-300 shrink-0">Fix →</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Revenue anchor ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Collected this month --}}
            <div class="bg-slate-800 rounded-xl border border-emerald-800/40 relative overflow-hidden shadow-lg shadow-emerald-950/40">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/25 to-transparent pointer-events-none"></div>
                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-500/60 to-transparent"></div>
                <div class="relative p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                        </div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Revenue this month</p>
                    </div>
                    <p class="stat-number text-4xl font-bold text-emerald-400 leading-none">R{{ number_format($completedRevenue, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-2">{{ $completedCount }} completed {{ Str::plural('appointment', $completedCount) }}</p>
                </div>
            </div>

            {{-- Outstanding --}}
            <div class="{{ $awaitingCount > 0 ? 'bg-amber-900/25 border-amber-700/50 shadow-amber-950/40' : 'bg-slate-800 border-slate-700/50 shadow-black/20' }} rounded-xl border relative overflow-hidden shadow-lg">
                <div class="absolute top-0 left-0 right-0 h-0.5 {{ $awaitingCount > 0 ? 'bg-gradient-to-r from-amber-500/60 to-transparent' : 'bg-gradient-to-r from-slate-600/40 to-transparent' }}"></div>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-lg {{ $awaitingCount > 0 ? 'bg-amber-500/20 border-amber-500/30' : 'bg-slate-700 border-slate-600' }} border flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 {{ $awaitingCount > 0 ? 'text-amber-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <p class="text-xs {{ $awaitingCount > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wider">Outstanding</p>
                    </div>
                    <p class="stat-number text-4xl font-bold {{ $awaitingCount > 0 ? 'text-amber-300' : 'text-slate-600' }} leading-none">R{{ number_format($awaitingTotal, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-2">
                        {{ $awaitingCount }} {{ Str::plural('appointment', $awaitingCount) }} awaiting payment
                        @if($awaitingCount > 0)
                            · <a href="{{ route('appointments.index', ['status' => 'awaiting_payment']) }}" class="text-amber-400 hover:text-amber-300">View →</a>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Total billed --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700/50 relative overflow-hidden shadow-lg shadow-black/20">
                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-[#0078D4]/40 to-transparent"></div>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-lg bg-[#0078D4]/15 border border-[#0078D4]/20 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        </div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Total billed</p>
                    </div>
                    <p class="stat-number text-4xl font-bold text-white leading-none">R{{ number_format($completedRevenue + $awaitingTotal, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-2">
                        <span class="text-emerald-500">R{{ number_format($completedRevenue, 2) }}</span> collected
                        @if($awaitingTotal > 0)
                            · <span class="text-amber-400">R{{ number_format($awaitingTotal, 2) }}</span> pending
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Operational stats ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="{{ route('appointments.index', ['date' => today()->toDateString()]) }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-all shadow-md shadow-black/20 border border-slate-700/50 border-t-2 border-t-[#0078D4]/40 group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Today</p>
                    <div class="w-7 h-7 rounded-lg bg-[#0078D4]/10 border border-[#0078D4]/15 flex items-center justify-center group-hover:bg-[#0078D4]/20 transition-colors">
                        <svg class="w-3.5 h-3.5 text-[#0078D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $todayCount }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ Str::plural('booking', $todayCount) }}</p>
            </a>

            <a href="{{ route('customers.index') }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-all shadow-md shadow-black/20 border border-slate-700/50 border-t-2 border-t-[#D4AF37]/40 group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Customers</p>
                    <div class="w-7 h-7 rounded-lg bg-[#D4AF37]/10 border border-[#D4AF37]/15 flex items-center justify-center group-hover:bg-[#D4AF37]/20 transition-colors">
                        <svg class="w-3.5 h-3.5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-3m-4 6H7a4 4 0 01-4-4v-2a4 4 0 014-4h1m4 0a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    </div>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $totalCustomers }}</p>
                <p class="text-xs text-slate-500 mt-1">total</p>
            </a>

            <a href="{{ route('staff.index') }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-all shadow-md shadow-black/20 border border-slate-700/50 border-t-2 border-t-emerald-500/40 group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Staff</p>
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/15 flex items-center justify-center group-hover:bg-emerald-500/20 transition-colors">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $activeStaff }}</p>
                <p class="text-xs text-slate-500 mt-1">active</p>
            </a>

            @if($hasPos)
            <a href="{{ route('stock.reorder-alerts') }}"
               class="{{ $reorderCount > 0 ? 'bg-amber-900/30 border-amber-700/50 border-t-amber-500/60' : 'bg-slate-800 border-slate-700/50 border-t-slate-600/40' }} hover:bg-slate-700/70 rounded-xl p-4 transition-all shadow-md shadow-black/20 border border-t-2 group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs {{ $reorderCount > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wider">Reorder</p>
                    <div class="w-7 h-7 rounded-lg {{ $reorderCount > 0 ? 'bg-amber-500/20 border-amber-500/30' : 'bg-slate-700 border-slate-600' }} border flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 {{ $reorderCount > 0 ? 'text-amber-400' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <p class="stat-number text-3xl font-bold {{ $reorderCount > 0 ? 'text-amber-300' : 'text-slate-600' }}">{{ $reorderCount }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $reorderCount > 0 ? 'need restocking' : 'all stocked' }}</p>
            </a>
            @else
            <a href="{{ route('services.index') }}"
               class="bg-slate-800 hover:bg-slate-700/70 rounded-xl p-4 transition-all shadow-md shadow-black/20 border border-slate-700/50 border-t-2 border-t-purple-500/40 group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Services</p>
                    <div class="w-7 h-7 rounded-lg bg-purple-500/10 border border-purple-500/15 flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                        <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <p class="stat-number text-3xl font-bold text-white">{{ $activeServices }}</p>
                <p class="text-xs text-slate-500 mt-1">active</p>
            </a>
            @endif

            @can('manage-tenants')
                <a href="{{ route('admin.module-requests.index') }}"
                   class="{{ $pendingModuleRequests > 0 ? 'bg-[#0078D4]/10 border-[#0078D4]/30 border-t-[#0078D4]/60' : 'bg-slate-800 border-slate-700/50 border-t-slate-600/40' }} hover:bg-slate-700/70 rounded-xl p-4 transition-all shadow-md shadow-black/20 border border-t-2 group">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-slate-400 uppercase tracking-wider">Requests</p>
                        <div class="w-7 h-7 rounded-lg bg-[#0078D4]/10 border border-[#0078D4]/15 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-[#0078D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>
                    <p class="stat-number text-3xl font-bold {{ $pendingModuleRequests > 0 ? 'text-[#0078D4]' : 'text-white' }}">{{ $pendingModuleRequests }}</p>
                    <p class="text-xs text-slate-500 mt-1">pending</p>
                </a>
            @endcan
        </div>

        <div class="grid lg:grid-cols-2 gap-6">

            {{-- Today's schedule --}}
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-xl shadow-black/25 border border-slate-700/50">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between bg-gradient-to-r from-[#0078D4]/10 to-transparent">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-md bg-[#0078D4]/20 border border-[#0078D4]/25 flex items-center justify-center">
                            <svg class="w-3 h-3 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-white">Today's Schedule</h3>
                    </div>
                    <a href="{{ route('appointments.create') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8] font-medium">+ New</a>
                </div>
                @forelse($upcomingToday as $appt)
                    <div class="px-4 py-3 border-b border-slate-700/50 flex items-center gap-3 hover:bg-slate-700/30 transition-colors">
                        <div class="text-xs text-slate-400 w-12 shrink-0 font-mono">{{ $appt->scheduled_at->format('H:i') }}</div>
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

            {{-- Recent bookings --}}
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-xl shadow-black/25 border border-slate-700/50">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between bg-gradient-to-r from-[#D4AF37]/8 to-transparent">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-md bg-[#D4AF37]/15 border border-[#D4AF37]/20 flex items-center justify-center">
                            <svg class="w-3 h-3 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-white">Recent Bookings</h3>
                    </div>
                    <a href="{{ route('appointments.index') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8] font-medium">View all →</a>
                </div>
                @forelse($recentAppointments as $appt)
                    <a href="{{ route('appointments.show', $appt) }}"
                       class="px-4 py-3 border-b border-slate-700/50 flex items-center gap-3 hover:bg-slate-700/30 transition-colors block">
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
