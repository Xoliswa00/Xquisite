<x-app-layout>
    <x-slot name="header">Platform Overview</x-slot>

@php
    $maxRev    = $revenueByMonth->max() ?: 1;
    $maxSig    = $signupsByMonth->max() ?: 1;
    $monthLabels = $months->map(fn($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M'));
@endphp

<div class="space-y-8">

    {{-- ═══════════════════════════════════════════════
         SECTION 1 — FINANCE
    ═══════════════════════════════════════════════ --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-[#D4AF37] mb-3">Finance</p>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">MRR</p>
                <p class="text-3xl font-bold text-[#D4AF37] mt-2">R{{ number_format($mrr, 0) }}</p>
                <p class="text-xs text-slate-500 mt-1.5">R{{ number_format($avgMrr, 0) }} avg per paying tenant</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Collected This Month</p>
                <p class="text-3xl font-bold text-emerald-400 mt-2">R{{ number_format($collectedThisMonth, 0) }}</p>
                @if($revenueGrowth !== null)
                    <p class="text-xs mt-1.5 flex items-center gap-1 {{ $revenueGrowth >= 0 ? 'text-emerald-500' : 'text-red-400' }}">
                        @if($revenueGrowth >= 0)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        @endif
                        {{ abs($revenueGrowth) }}% vs last month
                    </p>
                @else
                    <p class="text-xs text-slate-500 mt-1.5">First month of data</p>
                @endif
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Collection Rate</p>
                <p class="text-3xl font-bold mt-2 {{ $collectionRate >= 80 ? 'text-emerald-400' : ($collectionRate >= 50 ? 'text-amber-400' : 'text-red-400') }}">{{ $collectionRate }}%</p>
                <div class="mt-2 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $collectionRate >= 80 ? 'bg-emerald-500' : ($collectionRate >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width:{{ $collectionRate }}%"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">R{{ number_format($invoicedThisMonth, 0) }} invoiced this month</p>
            </div>

            <div class="{{ $overdueTotal > 0 ? 'bg-red-900/20 border-red-800/40' : 'bg-slate-900 border-slate-800' }} border rounded-2xl p-5">
                <p class="text-xs {{ $overdueTotal > 0 ? 'text-red-400' : 'text-slate-400' }} uppercase tracking-wide">Outstanding</p>
                <p class="text-3xl font-bold {{ $overdueTotal > 0 ? 'text-red-300' : 'text-slate-500' }} mt-2">R{{ number_format($outstandingTotal, 0) }}</p>
                <p class="text-xs text-slate-500 mt-1.5">
                    @if($overdueCount > 0)<span class="text-red-400">{{ $overdueCount }} overdue</span>@endif
                    @if($popPending > 0) · <a href="{{ route('admin.billing.index') }}" class="text-[#0078D4]">{{ $popPending }} POP to confirm</a>@endif
                    @if($overdueCount === 0 && $popPending === 0)<span class="text-emerald-500">All clear</span>@endif
                </p>
            </div>
        </div>
    </div>

    {{-- ── 6-month revenue trend (CSS bar chart) ── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-sm font-semibold text-white">Revenue Trend</h2>
                <p class="text-xs text-slate-500 mt-0.5">Monthly collected invoices — last 6 months</p>
            </div>
            <a href="{{ route('admin.billing.index') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8]">All invoices →</a>
        </div>
        <div class="flex items-end gap-2 h-36">
            @foreach($revenueByMonth as $month => $total)
                @php
                    $barPct = $maxRev > 0 ? ($total / $maxRev) * 100 : 0;
                    $label  = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M');
                    $isCurrent = $month === now()->format('Y-m');
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1.5 group" x-data>
                    <div class="text-xs text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                        R{{ number_format($total, 0) }}
                    </div>
                    <div class="w-full rounded-t-lg transition-all {{ $isCurrent ? 'bg-[#D4AF37]' : 'bg-[#0078D4]/60 group-hover:bg-[#0078D4]' }}"
                         style="height: {{ max($barPct, 2) }}%"></div>
                    <p class="text-xs {{ $isCurrent ? 'text-[#D4AF37] font-semibold' : 'text-slate-500' }}">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         SECTION 2 — GROWTH
    ═══════════════════════════════════════════════ --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-[#D4AF37] mb-3">Growth</p>
        <div class="grid lg:grid-cols-3 gap-4">

            {{-- Tenant status breakdown --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
                <h2 class="text-sm font-semibold text-white">Tenant Breakdown</h2>
                @php $total = max($totalTenants, 1); @endphp

                @foreach([
                    ['Paying', $paidTenants,      'bg-emerald-500', 'text-emerald-400'],
                    ['Trial',  $trialTenants,      'bg-[#D4AF37]',   'text-[#D4AF37]'],
                    ['Grace',  $graceTenants,      'bg-amber-500',   'text-amber-400'],
                    ['Suspended', $suspendedTenants, 'bg-red-500',   'text-red-400'],
                ] as [$label, $count, $bar, $text])
                    @php $pct = round(($count / $total) * 100); @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-slate-400">{{ $label }}</span>
                            <span class="{{ $text }} font-semibold">{{ $count }} <span class="text-slate-600 font-normal">({{ $pct }}%)</span></span>
                        </div>
                        <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full {{ $bar }} rounded-full" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach

                <div class="pt-2 border-t border-slate-800 flex justify-between text-xs">
                    <span class="text-slate-400">Total tenants</span>
                    <span class="text-white font-bold">{{ $totalTenants }}</span>
                </div>
            </div>

            {{-- Sign-up trend (CSS bar chart) --}}
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-white">New Sign-ups</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Tenants joined per month</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-[#0078D4]">{{ $newThisMonth }}</p>
                        <p class="text-xs text-slate-500">this month
                            @if($newLastMonth > 0)
                                <span class="{{ $newThisMonth >= $newLastMonth ? 'text-emerald-500' : 'text-red-400' }}">
                                    ({{ $newLastMonth }} last)
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-end gap-2 h-24">
                    @foreach($signupsByMonth as $month => $count)
                        @php
                            $barPct = $maxSig > 0 ? ($count / $maxSig) * 100 : 0;
                            $label  = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M');
                            $isCurrent = $month === now()->format('Y-m');
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1 group" x-data>
                            <div class="text-xs text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{ $count }}
                            </div>
                            <div class="w-full rounded-t {{ $isCurrent ? 'bg-[#D4AF37]' : 'bg-[#0078D4]/50 group-hover:bg-[#0078D4]' }} transition-all"
                                 style="height: {{ max($barPct, 4) }}%"></div>
                            <p class="text-xs {{ $isCurrent ? 'text-[#D4AF37] font-semibold' : 'text-slate-500' }}">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-3 border-t border-slate-800 grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <p class="text-slate-400">Platform users (active)</p>
                        <p class="text-white font-semibold mt-0.5">{{ number_format($totalUsers) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400">Trial → Paid conversion</p>
                        @php $conv = $totalTenants > 0 ? round(($paidTenants / $totalTenants) * 100) : 0; @endphp
                        <p class="text-white font-semibold mt-0.5">{{ $conv }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         SECTION 3 — OPERATIONS
    ═══════════════════════════════════════════════ --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-[#D4AF37] mb-3">Operations & Health</p>
        <div class="grid lg:grid-cols-3 gap-4">

            {{-- Module adoption --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Module Adoption</h2>
                @if($moduleStats->count())
                    @php $maxMod = $moduleStats->max('tenant_count') ?: 1; @endphp
                    <div class="space-y-3">
                        @foreach($moduleStats as $mod)
                            @php
                                $name = $mod->platformModule?->name ?? ucfirst(str_replace('_', ' ', $mod->module));
                                $pct  = round(($mod->tenant_count / $maxMod) * 100);
                                $ofAll = $totalTenants > 0 ? round(($mod->tenant_count / $totalTenants) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-300 truncate">{{ $name }}</span>
                                    <span class="text-white font-semibold ml-2 shrink-0">{{ $mod->tenant_count }} <span class="text-slate-500 font-normal">({{ $ofAll }}%)</span></span>
                                </div>
                                <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0078D4] rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 text-sm">No active modules yet.</p>
                @endif
            </div>

            {{-- System alerts --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
                <h2 class="text-sm font-semibold text-white mb-1">System Alerts</h2>

                @php
                    $alerts = [
                        ['overdueCount > 0', $overdueCount > 0, 'red', 'Overdue invoices', $overdueCount . ' invoice' . ($overdueCount !== 1 ? 's' : '') . ' past due', route('admin.billing.index')],
                        ['graceTenants > 0', $graceTenants > 0, 'amber', 'Tenants in grace', $graceTenants . ' at risk of suspension', route('admin.billing.index')],
                        ['popPending > 0', $popPending > 0, 'blue', 'POP awaiting confirmation', $popPending . ' proof' . ($popPending !== 1 ? 's' : '') . ' to review', route('admin.billing.index')],
                        ['billingQueueCount > 0', $billingQueueCount > 0, 'amber', 'Billing queue', $billingQueueCount . ' job' . ($billingQueueCount !== 1 ? 's' : '') . ' pending', route('admin.sync.index')],
                        ['pendingModReqs > 0', $pendingModReqs > 0, 'blue', 'Module requests', $pendingModReqs . ' pending approval', route('admin.module-requests.index')],
                        ['suspendedTenants > 0', $suspendedTenants > 0, 'red', 'Suspended accounts', $suspendedTenants . ' tenant' . ($suspendedTenants !== 1 ? 's' : '') . ' suspended', route('admin.billing.index')],
                    ];
                    $activeAlerts = array_filter($alerts, fn($a) => $a[1]);
                @endphp

                @if(count($activeAlerts))
                    @foreach($activeAlerts as [, , $color, $title, $detail, $url])
                        @php
                            $ring  = ['red' => 'border-red-700/60 bg-red-900/20', 'amber' => 'border-amber-700/60 bg-amber-900/20', 'blue' => 'border-[#0078D4]/30 bg-[#0078D4]/10'][$color];
                            $dot   = ['red' => 'bg-red-400', 'amber' => 'bg-amber-400', 'blue' => 'bg-[#0078D4]'][$color];
                            $txt   = ['red' => 'text-red-300', 'amber' => 'text-amber-300', 'blue' => 'text-[#0078D4]'][$color];
                        @endphp
                        <a href="{{ $url }}" class="flex items-start gap-3 p-3 rounded-xl border {{ $ring }} hover:opacity-80 transition-opacity">
                            <div class="w-2 h-2 rounded-full {{ $dot }} mt-1 shrink-0"></div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold {{ $txt }}">{{ $title }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $detail }}</p>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-emerald-800/40 bg-emerald-900/10">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs text-emerald-400">No active alerts — all systems healthy.</p>
                    </div>
                @endif
            </div>

            {{-- Needs attention list --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Needs Attention</h2>
                    <a href="{{ route('admin.billing.index') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8]">Billing →</a>
                </div>
                @forelse($attentionTenants as $t)
                    @php
                        if ($t->suspended_at)           { $pill = ['Suspended', 'bg-red-900/40 text-red-300 border-red-700']; }
                        elseif ($t->grace_period_ends_at) { $pill = ['Grace ' . $t->graceDaysLeft() . 'd', 'bg-amber-900/40 text-amber-300 border-amber-700']; }
                        else                             { $pill = ['Overdue', 'bg-orange-900/40 text-orange-300 border-orange-700']; }
                    @endphp
                    <a href="{{ route('admin.billing.show', $t) }}"
                       class="flex items-center gap-3 px-5 py-3 border-b border-slate-800/60 last:border-0 hover:bg-slate-800/40 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $t->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $t->email }}</p>
                        </div>
                        <span class="shrink-0 text-xs px-2 py-0.5 rounded-full border {{ $pill[1] }}">{{ $pill[0] }}</span>
                    </a>
                @empty
                    <div class="px-5 py-6 text-center text-slate-500 text-sm">All tenants in good standing.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         SECTION 4 — RELATIONSHIPS / RECENT
    ═══════════════════════════════════════════════ --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-[#D4AF37] mb-3">Relationships</p>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white">Recent Sign-ups</h2>
                <a href="{{ route('admin.tenants.index') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8]">All tenants →</a>
            </div>
            <div class="divide-y divide-slate-800/60">
                @forelse($recentTenants as $t)
                    <a href="{{ route('admin.tenants.show', $t) }}"
                       class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-800/40 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-[#002B5B] flex items-center justify-center text-xs font-bold text-[#D4AF37] shrink-0">
                            {{ strtoupper(substr($t->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $t->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $t->email }} · Joined {{ $t->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($t->tenantModules->count())
                                <span class="text-xs text-slate-400">{{ $t->tenantModules->count() }} {{ Str::plural('module', $t->tenantModules->count()) }}</span>
                            @endif
                            @if($t->isOnTrial())
                                <span class="text-xs px-2 py-0.5 rounded-full border bg-[#D4AF37]/10 text-[#D4AF37] border-[#D4AF37]/30">Trial</span>
                            @elseif($t->suspended_at)
                                <span class="text-xs px-2 py-0.5 rounded-full border bg-red-900/40 text-red-300 border-red-700">Suspended</span>
                            @elseif($t->is_active)
                                <span class="text-xs px-2 py-0.5 rounded-full border bg-emerald-900/40 text-emerald-300 border-emerald-700">Active</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-slate-500 text-sm">No tenants yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Quick actions ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('admin.billing.index'),         'Platform Billing',    'Invoices & payments',    'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
            [route('admin.tenants.index'),         'Tenants',             'Manage accounts',        'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            [route('admin.billing.settings'),      'Billing Settings',    'Grace & due dates',      'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            [route('admin.platform-modules.index'),'Platform Modules',    'Pricing & availability', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
        ] as [$url, $title, $sub, $path])
            <a href="{{ $url }}" class="flex items-center gap-3 bg-slate-900 border border-slate-800 hover:border-[#0078D4]/40 rounded-xl px-4 py-3 transition-colors group">
                <svg class="w-5 h-5 text-[#0078D4] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                <div>
                    <p class="text-xs font-medium text-white group-hover:text-[#0078D4] transition-colors">{{ $title }}</p>
                    <p class="text-xs text-slate-500">{{ $sub }}</p>
                </div>
            </a>
        @endforeach
    </div>

</div>
</x-app-layout>
