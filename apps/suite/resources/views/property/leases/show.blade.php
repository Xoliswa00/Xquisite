<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-white">Lease #{{ $lease->id }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    @if($lease->status === 'active') bg-emerald-900/40 text-emerald-400
                    @elseif($lease->status === 'pending') bg-yellow-900/40 text-yellow-400
                    @elseif($lease->status === 'terminated') bg-red-900/40 text-red-400
                    @else bg-slate-700 text-slate-400 @endif">
                    {{ ucfirst($lease->status) }}
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('leases.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Leases</a>
                @if($lease->status === 'pending')
                    <a href="{{ route('leases.edit', $lease) }}"
                       class="px-3 py-2 bg-indigo-700 hover:bg-indigo-600 text-white text-sm rounded-lg">Edit</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        {{-- Info Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase font-semibold mb-2">Property</p>
                <p class="text-slate-200 font-medium">{{ $lease->property?->name ?? '—' }}</p>
                <p class="text-slate-400 text-xs mt-0.5">{{ $lease->property?->address_line_1 }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase font-semibold mb-2">Unit</p>
                <p class="text-slate-200 font-medium">Unit {{ $lease->unit?->unit_number ?? '—' }}</p>
                <p class="text-slate-400 text-xs mt-0.5">{{ ucfirst($lease->unit?->type ?? '') }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase font-semibold mb-2">Renter</p>
                <p class="text-slate-200 font-medium">{{ $lease->renter?->name ?? '—' }}</p>
                <p class="text-slate-400 text-xs mt-0.5">{{ $lease->renter?->email }}</p>
            </div>
        </div>

        {{-- Lease Details --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Lease Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Start Date</p>
                    <p class="text-slate-200 mt-0.5">{{ \Carbon\Carbon::parse($lease->start_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">End Date</p>
                    <p class="text-slate-200 mt-0.5">{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('d M Y') : 'Month-to-month' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Monthly Rent</p>
                    <p class="text-indigo-400 font-semibold mt-0.5">R{{ number_format($lease->monthly_rent, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Deposit</p>
                    <p class="text-slate-200 mt-0.5">
                        {{ $lease->deposit_amount ? 'R'.number_format($lease->deposit_amount, 2) : '—' }}
                        @if($lease->deposit_paid)
                            <span class="ml-1 text-xs text-emerald-400">(Paid)</span>
                        @elseif($lease->deposit_amount)
                            <span class="ml-1 text-xs text-yellow-400">(Unpaid)</span>
                        @endif
                    </p>
                </div>
            </div>
            @if($lease->notes)
                <div class="mt-4 pt-4 border-t border-slate-700">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Notes</p>
                    <p class="text-slate-300 text-sm mt-1">{{ $lease->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Terminate Lease --}}
        @if($lease->status === 'active')
            <div class="bg-slate-800 rounded-xl p-6" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-300">Terminate Lease</h3>
                    <button type="button" @click="open = !open"
                            class="px-3 py-1.5 bg-red-900/40 hover:bg-red-800/50 text-red-400 text-xs rounded-lg border border-red-800">
                        Terminate Lease
                    </button>
                </div>
                <div x-show="open" x-transition class="mt-4 pt-4 border-t border-slate-700">
                    <form method="POST" action="{{ route('leases.terminate', $lease) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Termination Date *</label>
                                <input type="date" name="terminated_at" value="{{ date('Y-m-d') }}" required
                                       class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Reason for Termination</label>
                            <textarea name="termination_reason" rows="2"
                                      class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2"
                                      placeholder="Optional reason..."></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit"
                                    class="px-4 py-2 bg-red-700 hover:bg-red-600 text-white text-sm rounded-lg font-medium"
                                    onclick="return confirm('Are you sure you want to terminate this lease?')">
                                Confirm Termination
                            </button>
                            <button type="button" @click="open = false"
                                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Rent Payments --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">Rent Payments</h3>
            </div>
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-2 font-medium">Period</th>
                        <th class="px-4 py-2 font-medium">Amount Due</th>
                        <th class="px-4 py-2 font-medium">Amount Paid</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Due Date</th>
                        <th class="px-4 py-2 font-medium">Paid Date</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($lease->rentPayments as $payment)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 text-slate-200">{{ $payment->period }}</td>
                            <td class="px-4 py-2.5 text-slate-300">R{{ number_format($payment->amount_due, 2) }}</td>
                            <td class="px-4 py-2.5 text-slate-300">R{{ number_format($payment->amount_paid ?? 0, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($payment->status === 'paid') bg-emerald-900/40 text-emerald-400
                                    @elseif($payment->status === 'partial') bg-yellow-900/40 text-yellow-400
                                    @elseif($payment->status === 'overdue') bg-red-900/40 text-red-400
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ $payment->paid_date ? \Carbon\Carbon::parse($payment->paid_date)->format('d M Y') : '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                @if($payment->status !== 'paid')
                                    <a href="{{ route('rent-payments.record', $payment) }}"
                                       class="text-indigo-400 hover:text-indigo-300 text-xs">Record</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
