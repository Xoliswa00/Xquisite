<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <span>Tenants</span>
            <a href="{{ route('admin.tenants.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition-colors">
                + New Tenant
            </a>
        </div>
    </x-slot>

    <!-- Filters -->
    <form method="GET" class="flex gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Name, email, slug…"
               class="bg-slate-800 border border-slate-700 text-sm text-white rounded-xl px-4 py-2.5 w-72 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2.5 rounded-xl">Search</button>
        @if(request('search'))
            <a href="{{ route('admin.tenants.index') }}" class="text-slate-400 hover:text-white px-3 py-2.5 text-sm">Clear</a>
        @endif
    </form>

    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
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
                                <p class="text-xs text-indigo-400 mt-0.5">{{ $tenant->subdomain }}.{{ config('app.domain', 'xquisite.co.za') }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($tenant->activeModules as $mod)
                                    <span class="text-xs bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full">
                                        {{ config("modules.{$mod->module}.name") ?? $mod->module }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-500">None</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-white">
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
                               class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Manage →</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500 text-sm">No tenants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-slate-700">
            {{ $tenants->links() }}
        </div>
    </div>
</x-app-layout>
