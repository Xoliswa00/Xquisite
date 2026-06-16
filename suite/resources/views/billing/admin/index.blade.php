<x-app-layout>
    <x-slot name="header">Platform Billing — Admin</x-slot>

    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Heading row --}}
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h2 class="text-xl font-bold text-white">Platform Billing</h2>
            <div class="flex items-center gap-2 flex-wrap">
                @if($queueCount > 0)
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/20">
                        {{ $queueCount }} queued
                    </span>
                @endif
                <a href="{{ route('admin.billing.settings') }}"
                   class="px-3 py-1.5 text-sm border border-slate-700 text-slate-300 hover:bg-slate-700 rounded-lg transition-colors">
                    Settings
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="p-4 bg-slate-700/50 border border-slate-600 text-slate-300 rounded-xl text-sm">{{ session('info') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Total Tenants</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-slate-900 border border-amber-800 rounded-xl p-4">
                <p class="text-xs text-amber-400 uppercase tracking-wide">In Grace</p>
                <p class="text-2xl font-bold text-amber-300 mt-1">{{ $stats['grace'] }}</p>
            </div>
            <div class="bg-slate-900 border border-red-800 rounded-xl p-4">
                <p class="text-xs text-red-400 uppercase tracking-wide">Suspended</p>
                <p class="text-2xl font-bold text-red-300 mt-1">{{ $stats['suspended'] }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Unpaid Revenue</p>
                <p class="text-2xl font-bold text-white mt-1">R{{ number_format($stats['unpaid_rev'], 0) }}</p>
            </div>
        </div>

        {{-- Billing Waitlist --}}
        @if($dueTenants->isNotEmpty())
            <div class="bg-slate-900 border border-indigo-800/50 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-800 flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <p class="font-semibold text-white">Due for Billing This Month</p>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-medium">
                            {{ $dueTenants->count() }} tenant{{ $dueTenants->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('admin.billing.batch-generate') }}"
                          onsubmit="return confirm('Generate invoices for all {{ $dueTenants->count() }} tenant(s) now?')">
                        @csrf
                        <button class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">
                            Generate All Now
                        </button>
                    </form>
                </div>
                <div class="divide-y divide-slate-800">
                    @foreach($dueTenants as $tenant)
                        <div class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-slate-800/30">
                            <div class="min-w-0">
                                <p class="font-medium text-white text-sm">{{ $tenant->name }}</p>
                                <p class="text-xs text-slate-400">{{ $tenant->email }}</p>
                            </div>
                            <div class="flex items-center gap-4 shrink-0">
                                <span class="text-sm text-slate-300">
                                    R{{ number_format(\App\Models\Tenant::planAmount($tenant->plan ?? 'basic'), 2) }}
                                    <span class="text-xs text-slate-500 ml-1">{{ ucfirst($tenant->plan ?? 'basic') }}</span>
                                </span>
                                <a href="{{ route('admin.billing.show', ['company' => $tenant->id]) }}"
                                   class="text-xs px-3 py-1 border border-slate-700 text-slate-300 hover:bg-slate-700 rounded-lg">Manage</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tenants table --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-800">
                <p class="font-semibold text-white text-sm">All Tenants</p>
            </div>
            <table class="w-full text-sm">
                <thead class="border-b border-slate-800 text-slate-400 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-5 py-3">Tenant</th>
                        <th class="text-left px-5 py-3">Plan</th>
                        <th class="text-left px-5 py-3">Status</th>
                        <th class="text-left px-5 py-3">Unpaid</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($tenants as $tenant)
                        <tr class="hover:bg-slate-800/30">
                            <td class="px-5 py-4">
                                <p class="font-medium text-white">{{ $tenant->name }}</p>
                                <p class="text-xs text-slate-400">{{ $tenant->email }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-300">{{ ucfirst($tenant->plan ?? 'basic') }}</td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium border {{ $tenant->billingStatusClass() }}">
                                    {{ $tenant->billingStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-300">{{ $tenant->unpaid_count }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.billing.show', ['company' => $tenant->id]) }}" class="text-xs px-3 py-1.5 border border-slate-700 text-slate-300 hover:bg-slate-700 rounded-lg">Manage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $tenants->links() }}
    </div>
</x-app-layout>
