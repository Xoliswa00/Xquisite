<x-app-layout>
    <x-slot name="header">My Modules</x-slot>

    @if(session('info'))
        <div class="mb-6 px-5 py-4 rounded-xl bg-[#0078D4]/10 border border-[#0078D4]/30 text-[#B8D4F0] text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-[#0078D4] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary strip --}}
    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4">
            <p class="text-xs text-slate-400 mb-1">Active Modules</p>
            <p class="text-2xl font-bold text-white">{{ $tenant->activeModules->count() }}</p>
        </div>
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4">
            <p class="text-xs text-slate-400 mb-1">Monthly Subscription</p>
            <p class="text-2xl font-bold text-[#D4AF37]">R{{ number_format($tenant->monthlyTotal(), 2) }}</p>
        </div>
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4">
            <p class="text-xs text-slate-400 mb-1">Billing</p>
            <p class="text-sm font-semibold text-white mt-1">Monthly invoiced</p>
            <p class="text-xs text-slate-400">Invoiced on the 1st of each month</p>
        </div>
    </div>

    {{-- Live modules --}}
    @php
        $live    = $allModules->where('status', 'active');
        $beta    = $allModules->where('status', 'beta');
        $soon    = $allModules->where('status', 'coming_soon');
    @endphp

    @if ($live->isNotEmpty())
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Live
            </span>
            <span class="text-xs text-slate-500">Activates immediately after request</span>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($live as $key => $module)
                @php
                    $tenantModule   = $tenant->tenantModules->firstWhere('module', $key);
                    $isActive       = $tenantModule?->is_active ?? false;
                    $price          = $tenantModule?->price_override ?? $module->price;
                    $pendingRequest = $tenant->pendingModuleRequests->firstWhere('module', $key);
                @endphp
                <div class="bg-slate-800 rounded-2xl border {{ $isActive ? 'border-[#0078D4]/40' : 'border-slate-700' }} p-5 flex flex-col">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <h3 class="text-sm font-semibold text-[#D4AF37]">{{ $module->name }}</h3>
                                @if ($isActive)
                                    <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/20">Active</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">{{ $module->description }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-lg font-bold text-white">R{{ number_format($price, 0) }}</p>
                            <p class="text-xs text-slate-500">/month</p>
                        </div>
                    </div>

                    <div class="mt-auto pt-3">
                        @if ($isActive)
                            @if ($tenantModule?->activated_at)
                                <p class="text-[10px] text-slate-600 mb-3">Active since {{ $tenantModule->activated_at->format('d M Y') }}</p>
                            @endif
                            @if ($pendingRequest)
                                <div class="px-3 py-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300">
                                    A modification request is pending review.
                                </div>
                            @else
                                <form action="{{ route('settings.modules.request') }}" method="POST" class="space-y-2">
                                    @csrf
                                    <input type="hidden" name="module" value="{{ $key }}">
                                    <input type="hidden" name="type" value="modification">
                                    <textarea name="notes" rows="2"
                                              class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none"
                                              placeholder="Describe what you'd like changed…"></textarea>
                                    <button type="submit"
                                            class="w-full bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold py-2 rounded-lg transition-colors">
                                        Request Modification
                                    </button>
                                </form>
                            @endif
                        @else
                            @if ($pendingRequest)
                                <div class="px-3 py-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300">
                                    Activation request pending review.
                                </div>
                            @else
                                <form action="{{ route('settings.modules.request') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="module" value="{{ $key }}">
                                    <input type="hidden" name="type" value="activation">
                                    <button type="submit"
                                            class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                        Activate Module
                                    </button>
                                </form>
                                <p class="text-[10px] text-center text-slate-500 mt-1.5">
                                    {{ $module->auto_activate ? 'Activates instantly' : 'Reviewed by our team before activation' }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Beta modules --}}
    @if ($beta->isNotEmpty())
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>In Testing
            </span>
            <span class="text-xs text-slate-500">Join early access — reviewed before activation</span>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($beta as $key => $module)
                @php
                    $tenantModule   = $tenant->tenantModules->firstWhere('module', $key);
                    $isActive       = $tenantModule?->is_active ?? false;
                    $price          = $tenantModule?->price_override ?? $module->price;
                    $pendingRequest = $tenant->pendingModuleRequests->firstWhere('module', $key);
                @endphp
                <div class="bg-slate-800/60 rounded-2xl border {{ $isActive ? 'border-amber-500/30' : 'border-slate-700/60' }} p-5 flex flex-col">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <h3 class="text-sm font-semibold text-slate-200">{{ $module->name }}</h3>
                                <span class="text-[10px] bg-amber-500/15 text-amber-400 px-2 py-0.5 rounded-full border border-amber-500/20">Beta</span>
                                @if ($isActive)
                                    <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/20">Active</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $module->description }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-base font-bold text-slate-300">R{{ number_format($price, 0) }}</p>
                            <p class="text-xs text-slate-600">/month</p>
                        </div>
                    </div>
                    <div class="mt-auto pt-3">
                        @if ($isActive)
                            <p class="text-[10px] text-slate-600 text-center">Active — early access</p>
                        @elseif ($pendingRequest)
                            <div class="px-3 py-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300">
                                Early access request pending.
                            </div>
                        @else
                            <form action="{{ route('settings.modules.request') }}" method="POST">
                                @csrf
                                <input type="hidden" name="module" value="{{ $key }}">
                                <input type="hidden" name="type" value="activation">
                                <button type="submit"
                                        class="w-full bg-amber-600/80 hover:bg-amber-600 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                    Request Early Access
                                </button>
                            </form>
                            <p class="text-[10px] text-center text-slate-500 mt-1.5">Reviewed by our team before activation</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Coming soon modules --}}
    @if ($soon->isNotEmpty())
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-700 border border-slate-600 text-slate-400 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>Coming Soon
            </span>
            <span class="text-xs text-slate-600">On the roadmap</span>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($soon as $key => $module)
                <div class="bg-slate-800/30 rounded-2xl border border-slate-700/40 p-5 opacity-60">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-slate-400 mb-1">{{ $module->name }}</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $module->description }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-semibold text-slate-500">R{{ number_format($module->price, 0) }}</p>
                            <p class="text-xs text-slate-600">/month</p>
                        </div>
                    </div>
                    <div class="mt-4 text-center text-xs text-slate-600">
                        {{ $module->launch_date ? 'Est. ' . $module->launch_date->format('M Y') : 'Coming soon' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</x-app-layout>
