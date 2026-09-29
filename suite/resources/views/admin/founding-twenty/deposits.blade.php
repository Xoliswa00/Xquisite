<x-app-layout>
    <x-slot name="header">Founding 20 Deposit Journal</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Deposit journal</h2>
                <p class="text-slate-400 text-sm mt-1">Every movement of a deposit, as a balanced debit and credit. Entries are never edited or deleted. A mistake is corrected with a reversal, and both stay on the record.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.founding-twenty.deposits', ['format' => 'csv']) }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">Download CSV</a>
                <a href="{{ route('admin.founding-twenty.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">Back to applications</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['received' => ['Received', 'text-white'], 'refunded' => ['Paid back', 'text-slate-300'], 'credited' => ['Credited to invoices', 'text-slate-300'], 'held' => ['Still held (owed back)', 'text-[#D4AF37]']] as $key => [$label, $color])
                <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                    <p class="text-slate-400 text-sm">{{ $label }}</p>
                    <p class="text-2xl font-bold {{ $color }} mt-1">R{{ number_format($totals[$key], 2) }}</p>
                </div>
            @endforeach
        </div>

        @if($discrepancies->isNotEmpty())
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 rounded-xl p-4 text-sm">
                <p class="font-semibold">The journal and these applications disagree</p>
                <p class="mt-1">Someone changed a deposit without going through the ledger. Fix each one before relying on the totals.</p>
                <ul class="mt-2 space-y-1">
                    @foreach($discrepancies as $a)
                        <li><a href="{{ route('admin.founding-twenty.show', $a) }}" class="underline">{{ $a->business_name }}</a> ({{ $a->deposit_reference }})</li>
                    @endforeach
                </ul>
            </div>
        @else
            <p class="text-xs text-emerald-400">The journal agrees with every application's deposit record.</p>
        @endif

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[56rem]">
                    <thead class="bg-slate-900/50 border-b border-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-300">#</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-300">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-300">Entry</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-300">Business</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-300">Debit</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-300">Credit</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-300">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-300">By</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($entries as $e)
                            @php $undone = $e->reversal !== null; @endphp
                            <tr class="{{ $undone || $e->type === 'reversal' ? 'text-slate-500' : 'text-slate-300' }}">
                                <td class="px-4 py-3 font-mono">{{ $e->id }}</td>
                                <td class="px-4 py-3">{{ $e->entry_date->format('j M Y') }}</td>
                                <td class="px-4 py-3">
                                    {{ $e->description() }}@if($e->type === 'reversal') of #{{ $e->reverses_entry_id }}@endif
                                    @if($undone)<span class="text-xs">(reversed by #{{ $e->reversal->id }})</span>@endif
                                    <span class="block text-xs text-slate-500">
                                        {{ $e->reference }}@if($e->invoice) &middot; {{ $e->invoice->invoice_number }}@endif
                                        @if($e->reason) &middot; {{ $e->reason }}@endif
                                    </span>
                                </td>
                                <td class="px-4 py-3"><a href="{{ route('admin.founding-twenty.show', $e->founding_twenty_application_id) }}" class="hover:underline">{{ $e->application?->business_name }}</a></td>
                                <td class="px-4 py-3">{{ \App\Models\FoundingTwentyDepositEntry::ACCOUNTS[$e->debit_account] }}</td>
                                <td class="px-4 py-3">{{ \App\Models\FoundingTwentyDepositEntry::ACCOUNTS[$e->credit_account] }}</td>
                                <td class="px-4 py-3 text-right">R{{ number_format($e->amount, 2) }}</td>
                                <td class="px-4 py-3 text-xs">{{ $e->recorder?->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($e->type !== 'reversal' && ! $undone)
                                        <details>
                                            <summary class="cursor-pointer text-xs text-slate-400 hover:text-white">Reverse</summary>
                                            <form method="POST" action="{{ route('admin.founding-twenty.deposits.reverse', $e) }}" class="mt-2 space-y-2 text-left">
                                                @csrf
                                                <input type="text" name="reason" required minlength="5" maxlength="500" placeholder="Why is this being reversed?" class="w-56 bg-slate-900 border-slate-700 text-white rounded-lg text-xs">
                                                <button type="submit" class="px-3 py-1.5 text-xs bg-red-600/20 hover:bg-red-600/30 text-red-300 rounded transition">Reverse entry</button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-slate-400">No deposits confirmed yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
