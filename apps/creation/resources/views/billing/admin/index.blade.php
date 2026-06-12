<x-app-layout>
<div class="min-h-screen bg-slate-50">

    <div class="bg-slate-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">System Admin</p>
            <h1 class="text-3xl font-black tracking-tighter">Platform Billing</h1>
            <p class="text-slate-400 text-sm mt-1">Manage subscriptions and payments across all tenant companies.</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif

        {{-- STATS --}}
        @php
            $totalCompanies   = $companies->total();
            $suspendedCount   = \App\Models\Company::where('status', 'suspended')->count();
            $graceCount       = \App\Models\Company::where('status', 'active')->whereNotNull('grace_period_ends_at')->count();
            $unpaidTotal      = \App\Models\PlatformInvoice::whereIn('status', ['unpaid','overdue'])->sum('amount');
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Total Tenants',  'value' => $totalCompanies,              'color' => 'text-slate-900'],
                ['label' => 'In Grace',        'value' => $graceCount,                  'color' => 'text-amber-600'],
                ['label' => 'Suspended',       'value' => $suspendedCount,              'color' => 'text-rose-600'],
                ['label' => 'Unpaid Revenue',  'value' => 'R' . number_format($unpaidTotal, 0), 'color' => 'text-slate-900'],
            ] as $s)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $s['label'] }}</p>
                <p class="text-2xl font-black tabular-nums {{ $s['color'] }}">{{ $s['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- COMPANIES TABLE --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">All Tenant Companies</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-6 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Company</th>
                            <th class="text-left px-6 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Plan</th>
                            <th class="text-left px-6 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Status</th>
                            <th class="text-left px-6 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Renewal</th>
                            <th class="text-right px-6 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($companies as $company)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900">{{ $company->name }}</p>
                                <p class="text-xs text-slate-400">{{ $company->billing_email ?? $company->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-700 capitalize">
                                    {{ $company->subscription_plan ?? $company->plan ?? 'basic' }}
                                </span>
                                <p class="text-xs text-slate-400">R{{ number_format(\App\Models\Company::planAmount($company->subscription_plan ?? $company->plan ?? 'basic'), 0) }}/mo</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black border {{ $company->billingStatusClass() }}">
                                    {{ $company->billingStatusLabel() }}
                                    @if($company->isInGrace())
                                        ({{ $company->graceDaysLeft() }}d left)
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $company->subscription_renewal_date ? $company->subscription_renewal_date->format('d M Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.billing.show', $company) }}"
                                   class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors">
                                    Manage →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">No companies found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($companies->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $companies->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
</x-app-layout>
