<x-app-layout>
    <x-slot name="header">My Modules</x-slot>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Summary -->
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4">
            <p class="text-xs text-slate-400 mb-1">Active Modules</p>
            <p class="text-2xl font-bold text-white">{{ $tenant->activeModules->count() }}</p>
        </div>
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4">
            <p class="text-xs text-slate-400 mb-1">Monthly Subscription</p>
            <p class="text-2xl font-bold text-indigo-400">R{{ number_format($tenant->monthlyTotal(), 2) }}</p>
        </div>
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-4">
            <p class="text-xs text-slate-400 mb-1">Billing</p>
            <p class="text-sm font-semibold text-white mt-1">Monthly invoiced</p>
            <p class="text-xs text-slate-400">Invoiced on the 1st of each month</p>
        </div>
    </div>

    <!-- Module cards -->
    <div class="grid sm:grid-cols-2 gap-4">
        @foreach($allModules as $key => $module)
            @php
                $tenantModule = $tenant->tenantModules->firstWhere('module', $key);
                $isActive = $tenantModule?->is_active ?? false;
                $price    = $tenantModule?->price_override ?? $module['price'];
            @endphp

            <div class="bg-slate-800 rounded-2xl border {{ $isActive ? 'border-indigo-500/40' : 'border-slate-700' }} p-5">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-sm font-semibold text-white">{{ $module['name'] }}</h3>
                            @if($isActive)
                                <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full">Active</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400">{{ $module['description'] }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-lg font-bold text-white">R{{ number_format($price, 0) }}</p>
                        <p class="text-xs text-slate-400">per month</p>
                    </div>
                </div>

                @if($isActive)
                    <div class="text-xs text-slate-500 mb-3">
                        @if($tenantModule?->activated_at)
                            Active since {{ $tenantModule->activated_at->format('d M Y') }}
                        @endif
                    </div>
                    <div class="bg-indigo-500/5 border border-indigo-500/20 rounded-lg px-3 py-2 text-xs text-indigo-300">
                        This module is active. To make changes, contact support.
                    </div>
                @else
                    <form action="{{ route('settings.modules.request') }}" method="POST">
                        @csrf
                        <input type="hidden" name="module" value="{{ $key }}">
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                            Request Activation
                        </button>
                    </form>
                    <p class="text-xs text-center text-slate-500 mt-2">Our team will get in touch within 24 hours</p>
                @endif
            </div>
        @endforeach
    </div>
</x-app-layout>
