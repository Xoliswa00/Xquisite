<x-app-layout>
<div class="min-h-screen bg-slate-50">

    <div class="bg-slate-900 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="flex mb-3">
                <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    <li><a href="{{ route('admin.billing.index') }}" class="hover:text-slate-300">Platform Billing</a></li>
                    <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                    <li class="text-white">{{ $company->name }}</li>
                </ol>
            </nav>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tighter">{{ $company->name }}</h1>
                    <p class="text-slate-400 text-sm mt-1">{{ $company->billing_email ?? $company->email }}</p>
                </div>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-black border {{ $company->billingStatusClass() }}">
                    {{ $company->billingStatusLabel() }}
                </span>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif

        {{-- COMPANY STATUS PANEL --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Info --}}
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">Account Overview</h2>
                </div>
                <div class="px-6 py-5 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Plan</p>
                        <p class="font-bold text-slate-900 capitalize">{{ $company->subscription_plan ?? $company->plan ?? 'basic' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Monthly Fee</p>
                        <p class="font-bold text-slate-900">R{{ number_format(\App\Models\Company::planAmount($company->subscription_plan ?? $company->plan ?? 'basic'), 2) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Renewal Date</p>
                        <p class="font-bold text-slate-900">{{ $company->subscription_renewal_date ? $company->subscription_renewal_date->format('d M Y') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Last Billed</p>
                        <p class="font-bold text-slate-900">{{ $company->last_billing_date ? $company->last_billing_date->format('d M Y') : 'Never' }}</p>
                    </div>
                    @if($company->isInGrace())
                    <div class="col-span-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-1">Grace Period Ends</p>
                        <p class="font-bold text-amber-700">{{ $company->grace_period_ends_at->format('d M Y, H:i') }} ({{ $company->graceDaysLeft() }} days left)</p>
                    </div>
                    @endif
                    @if($company->suspended_at)
                    <div class="col-span-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-rose-600 mb-1">Suspended On</p>
                        <p class="font-bold text-rose-700">{{ $company->suspended_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">Actions</h2>
                </div>
                <div class="px-6 py-5 space-y-3">

                    {{-- Generate Invoice --}}
                    <form method="POST" action="{{ route('admin.billing.generate', $company) }}">
                        @csrf
                        <button type="submit"
                                class="w-full bg-slate-900 hover:bg-slate-700 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition-all">
                            Generate Invoice
                        </button>
                    </form>

                    @if($company->status === 'suspended')
                    {{-- Reactivate --}}
                    <form method="POST" action="{{ route('admin.billing.reactivate', $company) }}">
                        @csrf
                        <button type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl text-sm transition-all">
                            Reactivate Account
                        </button>
                    </form>
                    @else
                    {{-- Suspend --}}
                    <form method="POST" action="{{ route('admin.billing.suspend', $company) }}"
                          onsubmit="return confirm('Suspend {{ $company->name }}? This will block all their client access.')">
                        @csrf
                        <button type="submit"
                                class="w-full border border-rose-300 text-rose-600 hover:bg-rose-50 font-bold px-4 py-2.5 rounded-xl text-sm transition-all">
                            Suspend Account
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- INVOICES TABLE --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">Invoice History</h2>
            </div>

            @forelse($invoices as $invoice)
            <div class="border-b border-slate-100 last:border-0">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $invoice->invoice_number }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $invoice->billing_period_start->format('d M') }} – {{ $invoice->billing_period_end->format('d M Y') }}
                            @if($invoice->paid_at) · Paid {{ $invoice->paid_at->format('d M Y') }} via {{ $invoice->payment_method }} @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-black tabular-nums text-slate-900">R{{ number_format($invoice->amount, 2) }}</span>
                        <span class="text-xs font-black px-2 py-1 rounded-full border {{ $invoice->status_badge['class'] }}">
                            {{ $invoice->status_badge['label'] }}
                        </span>

                        @if(in_array($invoice->status, ['unpaid', 'overdue']))
                        <button onclick="document.getElementById('pay-form-{{ $invoice->id }}').classList.toggle('hidden')"
                                class="text-xs font-bold text-emerald-700 hover:text-emerald-900 transition-colors">
                            Mark Paid
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Mark Paid Form --}}
                @if(in_array($invoice->status, ['unpaid', 'overdue']))
                <div id="pay-form-{{ $invoice->id }}" class="hidden px-6 pb-4">
                    <form method="POST" action="{{ route('admin.billing.mark-paid', $invoice) }}"
                          class="bg-slate-50 border border-slate-200 rounded-xl p-4 grid grid-cols-2 gap-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Payment Method</label>
                            <input type="text" name="payment_method" placeholder="e.g. EFT, Card"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                                   required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Reference</label>
                            <input type="text" name="payment_reference" placeholder="Transaction reference"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                                   required>
                        </div>
                        <div class="col-span-2 flex justify-end">
                            <button type="submit"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2 rounded-xl text-sm transition-all">
                                Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="px-6 py-12 text-center text-slate-400 text-sm">No invoices generated yet.</div>
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
