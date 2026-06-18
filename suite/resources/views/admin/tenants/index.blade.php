<x-app-layout>
    <x-slot name="header">Tenants</x-slot>

    <div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
        <h1 class="text-xl font-bold text-[#D4AF37]">Tenants</h1>
        <a href="{{ route('admin.tenants.create') }}"
           class="shrink-0 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            + New Tenant
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Name, email, slug…"
               class="flex-1 min-w-[180px] bg-slate-800 border border-slate-700 text-sm text-white rounded-xl px-4 py-2.5 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
        <button type="submit" class="shrink-0 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-4 py-2.5 rounded-xl transition-colors">
            Search
        </button>
        @if(request('search'))
            <a href="{{ route('admin.tenants.index') }}" class="shrink-0 text-slate-400 hover:text-white px-3 py-2.5 text-sm">Clear</a>
        @endif
    </form>

    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">

        {{-- Mobile cards (hidden on sm+) --}}
        <div class="sm:hidden divide-y divide-slate-700">
            @forelse($tenants as $tenant)
                <div class="p-4 space-y-3">
                    {{-- Name + status --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-white truncate">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $tenant->email }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            @if($tenant->is_active)
                                <span class="inline-block text-xs text-emerald-400 font-medium">Active</span>
                            @else
                                <span class="inline-block text-xs text-red-400 font-medium">Inactive</span>
                            @endif
                            @if($tenant->isOnTrial())
                                <p class="text-[10px] text-amber-400 mt-0.5">Trial ends {{ $tenant->trial_ends_at->format('d M') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Meta row --}}
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <span class="text-slate-500">Slug</span>
                        <span class="font-mono text-slate-300">{{ $tenant->slug }}</span>
                        @if($tenant->subdomain)
                        <span class="text-slate-500">Subdomain</span>
                        <span class="text-[#0078D4] truncate">{{ $tenant->subdomain }}.{{ config('app.domain', 'xquisite.co.za') }}</span>
                        @endif
                        <span class="text-slate-500">Monthly</span>
                        <span class="font-semibold text-white">R{{ number_format($tenant->monthlyTotal(), 2) }}</span>
                        <span class="text-slate-500">Users</span>
                        <span class="text-slate-300">{{ $tenant->users_count }}</span>
                    </div>

                    {{-- Modules --}}
                    @if($tenant->activeModules->isNotEmpty())
                    <div class="flex flex-wrap gap-1">
                        @foreach($tenant->activeModules as $mod)
                            <span class="text-[10px] bg-[#0078D4]/20 text-[#B8D4F0] px-2 py-0.5 rounded-full">
                                {{ config("modules.{$mod->module}.name") ?? $mod->module }}
                            </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Action --}}
                    <a href="{{ route('admin.tenants.show', $tenant) }}"
                       class="inline-block text-xs font-medium text-[#0078D4] hover:text-[#B8D4F0]">
                        Manage →
                    </a>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-slate-500 text-sm">No tenants found.</div>
            @endforelse
        </div>

        {{-- Desktop table (hidden on mobile) --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-left text-xs text-slate-400">
                        <th class="px-5 py-3 font-medium">Tenant</th>
                        <th class="px-5 py-3 font-medium">Slug / Subdomain</th>
                        <th class="px-5 py-3 font-medium">Modules</th>
                        <th class="px-5 py-3 font-medium">Monthly</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Users</th>
                        <th class="px-5 py-3 font-medium w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-medium text-white">{{ $tenant->name }}</p>
                                <p class="text-xs text-slate-400">{{ $tenant->email }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-xs font-mono text-slate-300">{{ $tenant->slug }}</p>
                                @if($tenant->subdomain)
                                    <p class="text-xs text-[#0078D4] mt-0.5">{{ $tenant->subdomain }}.{{ config('app.domain', 'xquisite.co.za') }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($tenant->activeModules as $mod)
                                        <span class="text-xs bg-[#0078D4]/20 text-[#B8D4F0] px-2 py-0.5 rounded-full">
                                            {{ config("modules.{$mod->module}.name") ?? $mod->module }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500">None</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-white whitespace-nowrap">
                                R{{ number_format($tenant->monthlyTotal(), 2) }}
                            </td>
                            <td class="px-5 py-4">
                                @if($tenant->is_active)
                                    <span class="text-xs text-emerald-400 font-medium">Active</span>
                                @else
                                    <span class="text-xs text-red-400 font-medium">Inactive</span>
                                @endif
                                @if($tenant->isOnTrial())
                                    <p class="text-xs text-amber-400">Trial ends {{ $tenant->trial_ends_at->format('d M') }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-400">{{ $tenant->users_count }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.tenants.show', $tenant) }}"
                                   class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium whitespace-nowrap">Manage →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500 text-sm">No tenants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="px-5 py-4 border-t border-slate-700">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
