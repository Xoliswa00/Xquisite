<x-app-layout>
<div class="min-h-screen bg-slate-50">

    {{-- HERO --}}
    <div class="bg-slate-900 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">Platform Billing</p>
            <h1 class="text-3xl font-black tracking-tighter">Subscription & Invoices</h1>
            <p class="text-slate-400 text-sm mt-1">Manage your Creation platform subscription.</p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif

        {{-- CURRENT PLAN CARD --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">Current Plan</h2>
            </div>
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Plan</p>
                        <p class="text-xl font-black text-slate-900 capitalize">{{ $company->subscription_plan ?? $company->plan ?? 'Basic' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Monthly Cost</p>
                        <p class="text-xl font-black text-slate-900">
                            R{{ number_format(App\Models\Company::planAmount($company->subscription_plan ?? $company->plan ?? 'basic'), 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Account Status</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border {{ $company->billingStatusClass() }}">
                            {{ $company->billingStatusLabel() }}
                        </span>
                    </div>
                </div>

                @if($company->isInGrace())
                <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-black text-amber-900">Grace Period Active</p>
                        <span class="text-sm font-black text-amber-700">{{ $company->graceDaysLeft() }} {{ $company->graceDaysLeft() === 1 ? 'day' : 'days' }} left</span>
                    </div>
                    <div class="w-full bg-amber-200 rounded-full h-2">
                        @php $pct = max(0, min(100, (1 - ($company->graceDaysLeft() / 5)) * 100)); @endphp
                        <div class="bg-amber-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-amber-700 mt-2">Your service will be suspended after the grace period unless payment is received.</p>
                </div>
                @endif

                @if($company->status === 'suspended')
                <div class="mt-6 bg-rose-50 border border-rose-300 rounded-xl p-4">
                    <p class="text-sm font-black text-rose-900">Account Suspended</p>
                    <p class="text-xs text-rose-700 mt-1">Your service has been suspended due to non-payment. Pay your outstanding invoice to restore access.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- OUTSTANDING INVOICES --}}
        @if($unpaid->count() > 0)
        <div class="bg-white border border-rose-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-rose-100 bg-rose-50">
                <h2 class="font-black text-rose-900 text-sm uppercase tracking-wider">Outstanding Invoices</h2>
            </div>
            @foreach($unpaid as $inv)
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-50 last:border-0">
                <div>
                    <p class="text-sm font-black text-slate-900">{{ $inv->invoice_number }}</p>
                    <p class="text-xs text-slate-500">Due {{ $inv->due_date->format('d M Y') }}
                        @if($inv->isOverdue())
                            · <span class="text-rose-600 font-bold">{{ $inv->days_overdue }} days overdue</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-black text-slate-900">R{{ number_format($inv->amount, 2) }}</span>
                    <span class="text-xs font-black px-2 py-1 rounded-full border {{ $inv->status_badge['class'] }}">
                        {{ $inv->status_badge['label'] }}
                    </span>
                    <a href="{{ route('billing.show', $inv) }}"
                       class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors">View →</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ALL INVOICES --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">Invoice History</h2>
            </div>

            @forelse($invoices as $inv)
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ $inv->invoice_number }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $inv->billing_period_start->format('d M') }} – {{ $inv->billing_period_end->format('d M Y') }}
                        · {{ ucfirst($inv->plan) }} plan
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-black tabular-nums text-slate-900">R{{ number_format($inv->amount, 2) }}</span>
                    <span class="text-xs font-black px-2 py-1 rounded-full border {{ $inv->status_badge['class'] }}">
                        {{ $inv->status_badge['label'] }}
                    </span>
                    <a href="{{ route('billing.show', $inv) }}"
                       class="text-xs font-bold text-slate-400 hover:text-slate-700 transition-colors">View</a>
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center">
                <p class="text-slate-400 text-sm">No invoices yet.</p>
            </div>
            @endforelse

            @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
</x-app-layout>
