<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Service Agreement Charges</h2>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('service-agreement-charges.flag-overdue') }}">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-panel-2 hover:bg-line-2 text-ink text-sm rounded-lg">Flag Overdue</button>
                </form>
                <form method="POST" action="{{ route('service-agreement-charges.generate') }}">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg">Generate This Month</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="GET" class="flex gap-3">
            <select name="status" onchange="this.form.submit()" class="bg-panel-2 border border-line-2 text-ink text-sm rounded-lg px-3 py-2">
                <option value="">All statuses</option>
                @foreach(['pending', 'paid', 'partial', 'overdue'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>

        <div class="bg-panel-2 rounded-xl overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line-2 text-ink-muted text-left">
                        <th class="px-4 py-3 font-medium">Client</th>
                        <th class="px-4 py-3 font-medium">Agreement</th>
                        <th class="px-4 py-3 font-medium">Period</th>
                        <th class="px-4 py-3 font-medium">Amount Due</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Due Date</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-2">
                    @forelse($charges as $charge)
                        <tr class="hover:bg-line-2/40">
                            <td class="px-4 py-3 text-ink font-medium">{{ $charge->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-muted">{{ $charge->serviceAgreement?->plan_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-muted">{{ $charge->periodLabel() }}</td>
                            <td class="px-4 py-3 text-ink-muted">R{{ number_format($charge->amount_due, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($charge->status === 'paid') bg-emerald-900/40 text-emerald-400
                                    @elseif($charge->status === 'partial') bg-yellow-900/40 text-yellow-400
                                    @elseif($charge->status === 'overdue') bg-red-900/40 text-red-400
                                    @else bg-panel text-ink-muted @endif">
                                    {{ ucfirst($charge->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-ink-faint text-xs">{{ $charge->due_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($charge->serviceAgreement)
                                    <a href="{{ route('service-agreements.show', $charge->serviceAgreement) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View Agreement</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-ink-faint">No charges found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $charges->links() }}
    </div>
</x-app-layout>
