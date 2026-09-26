<x-app-layout>
    <x-slot name="header">Founding 20 Deposits</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Deposits</h2>
                <p class="text-slate-400 text-sm mt-1">Every deposit received and how it was settled. Money still held is what we owe back or as credit.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.founding-twenty.deposits', ['format' => 'csv']) }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">Download CSV</a>
                <a href="{{ route('admin.founding-twenty.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">Back to applications</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['received' => ['Received', 'text-white'], 'refunded' => ['Paid back', 'text-slate-300'], 'credited' => ['Credited to invoices', 'text-slate-300'], 'held' => ['Still held', 'text-[#D4AF37]']] as $key => [$label, $color])
                <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                    <p class="text-slate-400 text-sm">{{ $label }}</p>
                    <p class="text-2xl font-bold {{ $color }} mt-1">R{{ number_format($totals[$key], 2) }}</p>
                </div>
            @endforeach
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[42rem]">
                    <thead class="bg-slate-900/50 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-slate-300">Reference</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-300">Business</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-300">Amount</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-300">Received</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-300">Outcome</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-300">Settled</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($rows as $a)
                            <tr class="hover:bg-slate-700/50 transition">
                                <td class="px-6 py-3 font-mono text-slate-300">{{ $a->deposit_reference }}</td>
                                <td class="px-6 py-3"><a href="{{ route('admin.founding-twenty.show', $a) }}" class="text-white hover:underline">{{ $a->business_name }}</a></td>
                                <td class="px-6 py-3 text-right text-slate-300">R{{ number_format($a->deposit_amount, 2) }}</td>
                                <td class="px-6 py-3 text-slate-400">{{ $a->deposit_confirmed_at->format('j M Y') }}</td>
                                <td class="px-6 py-3 text-slate-300 capitalize">{{ $a->deposit_outcome ?? 'not chosen yet' }}</td>
                                <td class="px-6 py-3 text-slate-400">
                                    @if($a->deposit_refunded_at)
                                        Paid back {{ $a->deposit_refunded_at->format('j M Y') }}@if($a->deposit_refund_reference) (ref {{ $a->deposit_refund_reference }})@endif
                                    @elseif($a->deposit_credited_at)
                                        Credited {{ $a->deposit_credited_at->format('j M Y') }} to {{ $a->depositCreditInvoice?->invoice_number ?? 'an invoice' }}
                                    @else
                                        Held
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">No deposits confirmed yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
