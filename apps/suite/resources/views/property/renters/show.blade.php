<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $renter->name }}</h2>
                <a href="{{ route('renters.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Back to Renters</a>
            </div>
            <div class="flex gap-2">
                @if($renter->email && !$renter->password)
                    <form method="POST" action="{{ route('renters.invite', $renter) }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm rounded-lg">
                            Grant Portal Access
                        </button>
                    </form>
                @endif
                <a href="{{ route('renters.edit', $renter) }}"
                   class="px-3 py-2 bg-indigo-700 hover:bg-indigo-600 text-white text-sm rounded-lg">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Profile Card --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Profile</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Full Name</p>
                    <p class="text-slate-200 mt-0.5">{{ $renter->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Email</p>
                    <p class="text-slate-200 mt-0.5">{{ $renter->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Phone</p>
                    <p class="text-slate-200 mt-0.5">{{ $renter->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">ID Number</p>
                    <p class="text-slate-200 mt-0.5">{{ $renter->id_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Emergency Contact</p>
                    <p class="text-slate-200 mt-0.5">{{ $renter->emergency_contact_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Emergency Phone</p>
                    <p class="text-slate-200 mt-0.5">{{ $renter->emergency_contact_phone ?? '—' }}</p>
                </div>
                @if($renter->notes)
                <div class="col-span-full">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Notes</p>
                    <p class="text-slate-300 text-sm mt-0.5">{{ $renter->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Leases --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">Leases</h3>
            </div>
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-2 font-medium">Property</th>
                        <th class="px-4 py-2 font-medium">Unit</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Start</th>
                        <th class="px-4 py-2 font-medium">End</th>
                        <th class="px-4 py-2 font-medium">Rent</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($renter->leases as $lease)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 text-slate-200">{{ $lease->property?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-300">{{ $lease->unit?->unit_number ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($lease->status === 'active') bg-emerald-900/40 text-emerald-400
                                    @elseif($lease->status === 'pending') bg-yellow-900/40 text-yellow-400
                                    @elseif($lease->status === 'terminated') bg-red-900/40 text-red-400
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ ucfirst($lease->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ \Carbon\Carbon::parse($lease->start_date)->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('d M Y') : '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-300">R{{ number_format($lease->monthly_rent, 2) }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('leases.show', $lease) }}" class="text-indigo-400 hover:text-indigo-300 text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No leases.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Payments --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">Recent Payments</h3>
            </div>
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-2 font-medium">Period</th>
                        <th class="px-4 py-2 font-medium">Amount Due</th>
                        <th class="px-4 py-2 font-medium">Amount Paid</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Due Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($renter->rentPayments as $payment)
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
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
