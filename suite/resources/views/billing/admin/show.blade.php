<x-app-layout>
    <x-slot name="header">{{ $tenant->name }} — Billing</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.billing.index') }}" class="text-sm text-slate-400 hover:text-white">← Platform Billing</a>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.billing.generate', ['tenant' => $tenant->id]) }}">
                    @csrf
                    <button class="text-sm px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg">Generate Invoice</button>
                </form>
                @if($tenant->suspended_at)
                    <form method="POST" action="{{ route('admin.billing.reactivate', ['tenant' => $tenant->id]) }}">
                        @csrf
                        <button class="text-sm px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg">Reactivate</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.billing.suspend', ['tenant' => $tenant->id]) }}" onsubmit="return confirm('Suspend {{ $tenant->name }}?')">
                        @csrf
                        <button class="text-sm px-4 py-2 bg-red-800 hover:bg-red-700 text-white rounded-lg">Suspend</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-slate-400">Active Modules</p>
                <p class="text-white font-medium mt-0.5">{{ $tenant->activeModules()->count() }}</p>
            </div>
            <div>
                <p class="text-slate-400">Monthly Rate</p>
                <p class="text-white font-medium mt-0.5">R{{ number_format($tenant->monthlyTotal(), 2) }}</p>
            </div>
            <div>
                <p class="text-slate-400">Status</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium border mt-0.5 {{ $tenant->billingStatusClass() }}">{{ $tenant->billingStatusLabel() }}</span>
            </div>
            <div>
                <p class="text-slate-400">Grace Ends</p>
                <p class="text-white font-medium mt-0.5">{{ $tenant->grace_period_ends_at?->format('d M Y') ?? '—' }}</p>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-800 flex items-center justify-between">
                <p class="font-semibold text-white">Invoices</p>
            </div>
            @forelse($invoices as $invoice)
                @php $badge = $invoice->status_badge; @endphp
                <div class="px-5 py-4 border-b border-slate-800 last:border-0 {{ $invoice->isAwaitingConfirmation() ? 'bg-[#0078D4]/5' : 'hover:bg-slate-800/30' }} space-y-3">
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <p class="font-medium text-white">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-slate-400">{{ $invoice->billing_period_start->format('d M') }} – {{ $invoice->billing_period_end->format('d M Y') }} · Due {{ $invoice->due_date->format('d M Y') }}</p>
                        </div>
                        <div class="text-right mr-2">
                            <p class="text-white">R{{ number_format($invoice->amount, 2) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full border {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        </div>
                        <a href="{{ route('admin.billing.pdf.admin', $invoice) }}" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg">PDF</a>
                        @if(in_array($invoice->status, ['unpaid', 'overdue']))
                            <form method="POST" action="{{ route('admin.billing.mark-paid', $invoice) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="text" name="payment_method" placeholder="Method" required class="w-24 text-xs rounded bg-slate-800 border-slate-700 text-slate-200 px-2 py-1.5">
                                <input type="text" name="payment_reference" placeholder="Reference" required class="w-28 text-xs rounded bg-slate-800 border-slate-700 text-slate-200 px-2 py-1.5">
                                <button class="text-xs px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg">Mark Paid</button>
                            </form>
                        @else
                            <span class="text-xs text-slate-500">{{ $invoice->paid_at?->format('d M Y') }}</span>
                        @endif
                    </div>

                    @if($invoice->isAwaitingConfirmation())
                        <div class="flex items-center gap-3 bg-[#0078D4]/10 border border-[#0078D4]/30 rounded-xl px-4 py-2.5">
                            <svg class="w-4 h-4 text-[#0078D4] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <p class="text-xs text-[#0078D4] flex-1">POP submitted {{ $invoice->pop_uploaded_at->format('d M Y \a\t H:i') }}</p>
                            <a href="{{ route('admin.billing.pop.admin-download', $invoice) }}" class="text-xs font-medium text-[#0078D4] hover:text-[#0065B8] underline">Download POP</a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">No invoices yet.</div>
            @endforelse
        </div>

        {{ $invoices->links() }}
    </div>
</x-app-layout>
