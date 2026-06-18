<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('payment-plans.index') }}" class="text-slate-500 hover:text-slate-400">← Payment Plans</a>
            <span class="text-slate-500">/</span>
            <h2 class="text-xl font-semibold">{{ $paymentPlan->title }}</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('success'))
            <div class="p-4 bg-emerald-900/50 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        {{-- Summary card --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Total</p>
                    <p class="text-2xl font-bold text-white">R{{ number_format($paymentPlan->total_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Paid</p>
                    <p class="text-2xl font-bold text-emerald-600">R{{ number_format($paymentPlan->amount_paid, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Outstanding</p>
                    <p class="text-2xl font-bold {{ $paymentPlan->amountOutstanding() > 0 ? 'text-amber-600' : 'text-slate-500' }}">
                        R{{ number_format($paymentPlan->amountOutstanding(), 2) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Status</p>
                    <span class="inline-block mt-1 text-sm px-2.5 py-1 rounded-full font-medium
                        {{ $paymentPlan->status === 'active'    ? 'bg-[#E8F2FA] text-[#002B5B]' : '' }}
                        {{ $paymentPlan->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $paymentPlan->status === 'cancelled' ? 'bg-slate-800/50 text-slate-400' : '' }}
                        {{ $paymentPlan->status === 'defaulted' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($paymentPlan->status) }}
                    </span>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mt-5">
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>{{ $paymentPlan->progressPercent() }}% paid</span>
                    @if ($paymentPlan->cancellation_fee > 0)
                        <span>Cancellation fee: R{{ number_format($paymentPlan->cancellation_fee, 2) }}</span>
                    @endif
                </div>
                <div class="w-full bg-slate-700 rounded-full h-2">
                    <div class="bg-[#0078D4] h-2 rounded-full transition-all" style="width: {{ $paymentPlan->progressPercent() }}%"></div>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-4 text-sm text-slate-500">
                @if ($paymentPlan->customer)
                    <span>👤 {{ $paymentPlan->customer->name }}</span>
                @endif
                <span>📋 {{ ucfirst(str_replace('_', ' ', $paymentPlan->type)) }}</span>
                @if ($paymentPlan->notes)
                    <span class="italic">{{ $paymentPlan->notes }}</span>
                @endif
            </div>
        </div>

        {{-- Installments --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-700/50">
                <h3 class="font-semibold text-[#D4AF37]">Payment Schedule</h3>
            </div>

            <div class="divide-y divide-slate-700/50">
                @foreach ($paymentPlan->installments as $installment)
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                        {{ $installment->status === 'paid'    ? 'bg-emerald-100' : '' }}
                        {{ $installment->status === 'pending' && !$installment->isOverdue() ? 'bg-slate-800/50' : '' }}
                        {{ $installment->status === 'pending' && $installment->isOverdue()  ? 'bg-red-100' : '' }}
                        {{ $installment->status === 'waived'  ? 'bg-slate-800/50' : '' }}">
                        @if ($installment->status === 'paid')
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @elseif ($installment->isOverdue())
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <span class="text-xs text-slate-500 font-medium">{{ $installment->installment_number }}</span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white">{{ $installment->label }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Due {{ $installment->due_date->format('d M Y') }}
                            @if ($installment->paid_at)
                                · Paid {{ $installment->paid_at->format('d M Y') }}
                                via {{ ucfirst($installment->payment_method) }}
                                @if ($installment->reference) · {{ $installment->reference }} @endif
                            @endif
                        </p>
                    </div>

                    <p class="font-semibold text-white text-sm">R{{ number_format($installment->amount, 2) }}</p>

                    @if ($installment->status === 'pending' && $paymentPlan->status === 'active')
                    <form method="POST" action="{{ route('payment-plans.pay', $installment) }}"
                          class="flex items-center gap-2 shrink-0">
                        @csrf
                        <select name="payment_method"
                                class="text-xs border border-slate-700 rounded-lg px-2 py-1.5 text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                            <option value="cash">Cash</option>
                            <option value="eft">EFT</option>
                            <option value="card">Card</option>
                        </select>
                        <button type="submit"
                                class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg font-medium">
                            Mark paid
                        </button>
                    </form>
                    @elseif ($installment->status === 'paid')
                        <span class="text-xs text-emerald-600 font-medium shrink-0">Paid ✓</span>
                    @elseif ($installment->status === 'waived')
                        <span class="text-xs text-slate-500 shrink-0">Waived</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Cancel plan --}}
        @if ($paymentPlan->status === 'active')
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-5">
            <h3 class="font-medium text-[#D4AF37] mb-3">Cancel Plan</h3>
            <form method="POST" action="{{ route('payment-plans.cancel', $paymentPlan) }}"
                  onsubmit="return confirm('Cancel this plan? This cannot be undone.')">
                @csrf @method('PATCH')
                <div class="flex gap-3">
                    <input type="text" name="reason" placeholder="Reason (optional)"
                           class="flex-1 border border-slate-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                    <button type="submit"
                            class="px-4 py-2 text-sm text-red-600 border border-red-200 hover:bg-red-50 rounded-lg font-medium transition">
                        Cancel Plan
                    </button>
                </div>
                @if ($paymentPlan->cancellation_fee > 0)
                    <p class="text-xs text-amber-600 mt-2">
                        ⚠ Cancellation fee of R{{ number_format($paymentPlan->cancellation_fee, 2) }} applies and must be collected manually.
                    </p>
                @endif
            </form>
        </div>
        @endif

    </div>
</x-app-layout>
