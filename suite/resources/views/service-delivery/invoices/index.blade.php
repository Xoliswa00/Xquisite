<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Invoices</h2>
            <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">+ New Invoice</a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="GET" class="flex gap-3">
            <select name="status" onchange="this.form.submit()" class="bg-panel-2 border border-line-2 text-ink text-sm rounded-lg px-3 py-2">
                <option value="">All statuses</option>
                @foreach(['draft', 'sent', 'paid', 'overdue', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @if(request('status'))
                <a href="{{ route('invoices.index') }}" class="text-sm px-4 py-2 rounded-lg text-ink-muted hover:text-ink border border-line-2">Clear</a>
            @endif
        </form>

        <div class="bg-panel-2 rounded-xl overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line-2 text-ink-muted text-left">
                        <th class="px-4 py-3 font-medium">Number</th>
                        <th class="px-4 py-3 font-medium">Client</th>
                        <th class="px-4 py-3 font-medium">Issue Date</th>
                        <th class="px-4 py-3 font-medium">Due Date</th>
                        <th class="px-4 py-3 font-medium">Total</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-2">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-line-2/40 {{ $invoice->isOverdue() ? 'bg-red-900/10' : '' }}">
                            <td class="px-4 py-3 text-ink font-medium">{{ $invoice->invoice_number }}</td>
                            <td class="px-4 py-3 text-ink-muted">{{ $invoice->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-faint text-xs">{{ $invoice->issue_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-ink-faint text-xs">{{ $invoice->due_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-ink-muted">R{{ number_format($invoice->total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($invoice->status === 'paid') bg-emerald-900/40 text-emerald-400
                                    @elseif($invoice->isOverdue()) bg-red-900/40 text-red-400
                                    @elseif($invoice->status === 'sent') bg-yellow-900/40 text-yellow-400
                                    @elseif($invoice->status === 'cancelled') bg-panel text-ink-faint
                                    @else bg-panel-2 text-ink-muted border border-line-2 @endif">
                                    {{ $invoice->isOverdue() ? 'Overdue' : ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-ink-faint">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $invoices->links() }}
    </div>
</x-app-layout>
