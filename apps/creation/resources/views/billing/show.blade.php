<x-app-layout>
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-6">

    <div class="border-b border-slate-200 pb-6">
        <nav class="flex mb-2">
            <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                <li><a href="{{ route('billing.index') }}" class="hover:text-slate-700">Billing</a></li>
                <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li class="text-slate-900">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">{{ $invoice->invoice_number }}</h2>
                <p class="text-slate-400 text-sm mt-0.5">{{ ucfirst($invoice->plan) }} Plan · {{ $invoice->billing_period_start->format('d M') }} – {{ $invoice->billing_period_end->format('d M Y') }}</p>
            </div>
            <span class="text-xs font-black px-3 py-1.5 rounded-full border {{ $invoice->status_badge['class'] }}">
                {{ $invoice->status_badge['label'] }}
            </span>
        </div>
    </div>

    {{-- INVOICE DETAIL --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 text-slate-500">Invoice Number</td>
                        <td class="py-3 font-bold text-slate-900 text-right">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-500">Plan</td>
                        <td class="py-3 font-bold text-slate-900 text-right capitalize">{{ $invoice->plan }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-500">Billing Period</td>
                        <td class="py-3 font-bold text-slate-900 text-right">
                            {{ $invoice->billing_period_start->format('d M Y') }} – {{ $invoice->billing_period_end->format('d M Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-500">Amount</td>
                        <td class="py-3 font-black text-slate-900 text-right text-lg">R{{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-500">Due Date</td>
                        <td class="py-3 font-bold text-right {{ $invoice->isOverdue() ? 'text-rose-600' : 'text-slate-900' }}">
                            {{ $invoice->due_date->format('d M Y') }}
                            @if($invoice->isOverdue())
                                <span class="ml-1 text-xs">({{ $invoice->days_overdue }} days overdue)</span>
                            @endif
                        </td>
                    </tr>
                    @if($invoice->paid_at)
                    <tr>
                        <td class="py-3 text-slate-500">Paid On</td>
                        <td class="py-3 font-bold text-emerald-700 text-right">{{ $invoice->paid_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-500">Payment Method</td>
                        <td class="py-3 font-bold text-slate-900 text-right">{{ $invoice->payment_method }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-slate-500">Reference</td>
                        <td class="py-3 font-bold text-slate-900 text-right font-mono">{{ $invoice->payment_reference }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if(in_array($invoice->status, ['unpaid', 'overdue']))
        <div class="px-6 py-5 bg-amber-50 border-t border-amber-100">
            <p class="text-sm font-black text-amber-900 mb-2">Payment Instructions</p>
            <p class="text-xs text-amber-800">
                Please make an EFT payment to the following account and email your proof of payment to
                <strong>billing@creation.app</strong> with your invoice number as the reference.
            </p>
            <div class="mt-3 bg-white border border-amber-200 rounded-xl p-4 text-xs space-y-1 font-mono">
                <p><span class="text-slate-500">Bank:</span> <span class="font-bold text-slate-900">FNB</span></p>
                <p><span class="text-slate-500">Account Name:</span> <span class="font-bold text-slate-900">Creation (Pty) Ltd</span></p>
                <p><span class="text-slate-500">Account Number:</span> <span class="font-bold text-slate-900">62000000000</span></p>
                <p><span class="text-slate-500">Branch Code:</span> <span class="font-bold text-slate-900">250655</span></p>
                <p><span class="text-slate-500">Reference:</span> <span class="font-bold text-slate-900">{{ $invoice->invoice_number }}</span></p>
            </div>
        </div>
        @endif
    </div>

    <a href="{{ route('billing.index') }}" class="inline-block text-sm font-bold text-slate-500 hover:text-slate-900">← Back to Billing</a>

</div>
</x-app-layout>
