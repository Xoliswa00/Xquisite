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
            @php
                $bankName    = \App\Models\BillingSetting::get('bank_name');
                $bankAccName = \App\Models\BillingSetting::get('bank_account_name');
                $bankAccNum  = \App\Models\BillingSetting::get('bank_account_number');
                $bankBranch  = \App\Models\BillingSetting::get('bank_branch_code');
                $billingEmail = \App\Models\BillingSetting::get('company_email');
            @endphp
            @if($bankName || $bankAccNum)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <h3 class="font-semibold text-[#D4AF37] mb-3">EFT Payment Details</h3>
                    <div class="space-y-2 text-sm text-slate-300">
                        @if($bankName)    <p><span class="text-slate-400">Bank:</span> {{ $bankName }}</p>@endif
                        @if($bankAccName) <p><span class="text-slate-400">Account Name:</span> {{ $bankAccName }}</p>@endif
                        @if($bankAccNum)  <p><span class="text-slate-400">Account Number:</span> {{ $bankAccNum }}</p>@endif
                        @if($bankBranch)  <p><span class="text-slate-400">Branch Code:</span> {{ $bankBranch }}</p>@endif
                        <p><span class="text-slate-400">Reference:</span> <strong class="text-amber-300">{{ $invoice->invoice_number }}</strong></p>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">
                        Use your invoice number as payment reference.
                        @if($billingEmail) Email proof of payment to <span class="text-slate-400">{{ $billingEmail }}</span> or upload it directly from the billing page.@endif
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('billing.pdf', $invoice) }}" class="inline-flex items-center gap-2 text-sm px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Invoice PDF
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- POP upload / submitted state --}}
        @if(in_array($invoice->status, ['unpaid', 'overdue']))
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h3 class="font-semibold text-white mb-3">Upload Proof of Payment</h3>
                @if($invoice->isAwaitingConfirmation())
                    <div class="flex items-center gap-3 bg-[#0078D4]/10 border border-[#0078D4]/30 rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-[#0078D4] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-[#0078D4]">Proof of payment submitted</p>
                            <p class="text-xs text-[#0078D4]/70 mt-0.5">Uploaded {{ $invoice->pop_uploaded_at->format('d M Y \a\t H:i') }} · We will confirm within 1–2 business days.</p>
                        </div>
                        <a href="{{ route('billing.pop.download', $invoice) }}" class="text-xs text-[#0078D4] hover:text-[#0065B8] underline whitespace-nowrap">View file</a>
                    </div>
                @else
                    <div class="bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-3">
                        <p class="text-xs text-slate-400 mb-2">Paid via EFT? Upload your bank proof of payment.</p>
                        <form method="POST" action="{{ route('billing.pop.upload', $invoice) }}" enctype="multipart/form-data" class="flex items-center gap-3">
                            @csrf
                            <label class="flex-1 cursor-pointer">
                                <input type="file" name="pop_file" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#0078D4]/20 file:text-[#0078D4] hover:file:bg-[#0078D4]/30 cursor-pointer">
                            </label>
                            <button type="submit" class="shrink-0 text-xs px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg transition-colors">Submit POP</button>
                        </form>
                        <p class="text-xs text-slate-500 mt-1.5">PDF, JPG or PNG · Max 5 MB</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
