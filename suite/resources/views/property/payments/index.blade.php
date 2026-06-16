<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Rent Payments</h2>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('rent-payments.generate') }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">
                        Generate Monthly
                    </button>
                </form>
                <form method="POST" action="{{ route('rent-payments.flag-overdue') }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-2 bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm rounded-lg border border-red-800">
                        Flag Overdue
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('rent-payments.index') }}" class="bg-slate-800 rounded-xl p-4">
            <div class="flex gap-3 flex-wrap">
                <select name="status" class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    <option value="">All Statuses</option>
                    @foreach(['pending','paid','partial','overdue'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <input type="text" name="period" value="{{ request('period') }}"
                       placeholder="Period (YYYY-MM)"
                       class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 placeholder-slate-500 w-40">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg">Filter</button>
                @if(request()->hasAny(['status','period']))
                    <a href="{{ route('rent-payments.index') }}"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Period</th>
                        <th class="px-4 py-3 font-medium">Property / Unit</th>
                        <th class="px-4 py-3 font-medium">Renter</th>
                        <th class="px-4 py-3 font-medium">Due Date</th>
                        <th class="px-4 py-3 font-medium">Amount Due</th>
                        <th class="px-4 py-3 font-medium">Amount Paid</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-slate-200 font-medium">{{ $payment->period }}</td>
                            <td class="px-4 py-3">
                                <p class="text-slate-200">{{ $payment->lease?->property?->name ?? '—' }}</p>
                                <p class="text-slate-500 text-xs">Unit {{ $payment->unit?->unit_number ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $payment->renter?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-300">R{{ number_format($payment->amount_due, 2) }}</td>
                            <td class="px-4 py-3 text-slate-300">R{{ number_format($payment->amount_paid ?? 0, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($payment->status === 'paid') bg-emerald-900/40 text-emerald-400
                                    @elseif($payment->status === 'partial') bg-yellow-900/40 text-yellow-400
                                    @elseif($payment->status === 'overdue') bg-red-900/40 text-red-400
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('rent-payments.show', $payment) }}"
                                       class="text-indigo-400 hover:text-indigo-300 text-xs">View</a>
                                    @if($payment->status !== 'paid')
                                        <a href="{{ route('rent-payments.record', $payment) }}"
                                           class="text-emerald-400 hover:text-emerald-300 text-xs">Record</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-500">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $payments->withQueryString()->links() }}

    </div>
</x-app-layout>
