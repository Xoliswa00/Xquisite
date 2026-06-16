<x-app-layout>
    <x-slot name="header">Invoice {{ $invoice->invoice_number }}</x-slot>

    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('billing.index') }}" class="text-sm text-slate-400 hover:text-white">← Billing</a>
            @php $badge = $invoice->status_badge; @endphp
            <span class="px-3 py-1.5 rounded-full text-sm font-medium border {{ $badge['class'] }}">{{ $badge['label'] }}</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-400">Invoice Number</p>
                    <p class="text-white font-medium mt-0.5">{{ $invoice->invoice_number }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Plan</p>
                    <p class="text-white font-medium mt-0.5">{{ ucfirst($invoice->plan) }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Billing Period</p>
                    <p class="text-white font-medium mt-0.5">{{ $invoice->billing_period_start->format('d M Y') }} – {{ $invoice->billing_period_end->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Due Date</p>
                    <p class="text-white font-medium mt-0.5">{{ $invoice->due_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Amount</p>
                    <p class="text-2xl font-bold text-white mt-0.5">R{{ number_format($invoice->amount, 2) }}</p>
                </div>
                @if($invoice->paid_at)
                    <div>
                        <p class="text-slate-400">Paid</p>
                        <p class="text-emerald-400 font-medium mt-0.5">{{ $invoice->paid_at->format('d M Y') }} via {{ $invoice->payment_method }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if(in_array($invoice->status, ['unpaid', 'overdue']))
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="font-semibold text-white mb-3">Payment Instructions (EFT)</h3>
                <div class="space-y-2 text-sm text-slate-300">
                    <p><span class="text-slate-400">Bank:</span> First National Bank</p>
                    <p><span class="text-slate-400">Account Name:</span> Xquisite Suite (Pty) Ltd</p>
                    <p><span class="text-slate-400">Account Number:</span> 62xxxxxxxx</p>
                    <p><span class="text-slate-400">Branch Code:</span> 250655</p>
                    <p><span class="text-slate-400">Reference:</span> <strong class="text-amber-300">{{ $invoice->invoice_number }}</strong></p>
                </div>
                <p class="text-xs text-slate-500 mt-3">Please use your invoice number as payment reference. Email proof to billing@xquisite.co.za.</p>
            </div>
        @endif
    </div>
</x-app-layout>
