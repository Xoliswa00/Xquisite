<x-app-layout>
    <x-slot name="header">Unit {{ $unit->unit_number }}</x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-[#D4AF37]">Unit {{ $unit->unit_number }}</h2>
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($unit->status === 'occupied') bg-emerald-900/40 text-emerald-400
                            @elseif($unit->status === 'vacant') bg-yellow-900/40 text-yellow-400
                            @else bg-yellow-900/40 text-yellow-400 @endif">
                            {{ ucfirst($unit->status) }}
                        </span>
                    </div>
                    <a href="{{ route('properties.units.index', $property) }}" class="text-sm text-slate-400 hover:text-white mt-0.5 inline-block">&larr; {{ $property->name }}</a>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if($unit->status === 'vacant')
                        <a href="{{ route('leases.create', ['unit_id' => $unit->id]) }}"
                           class="px-3 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm rounded-lg">
                            + Create Lease
                        </a>
                    @endif
                    <a href="{{ route('properties.units.edit', [$property, $unit]) }}"
                       class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
                </div>
            </div>
            @if($unit->status === 'vacant' && $property->tenant)
                @php $applyLink = route('apply.show', [$property->tenant->slug, $property]) . '?unit=' . $unit->id; @endphp
                <div class="flex items-center gap-2 bg-slate-900 rounded-lg px-3 py-2 mt-4" x-data="{ copied: false }">
                    <span class="text-xs text-slate-400 uppercase font-semibold shrink-0">Application Link</span>
                    <span class="text-xs text-slate-300 truncate flex-1">{{ $applyLink }}</span>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $applyLink }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            class="shrink-0 text-xs font-semibold px-2 py-1 rounded-md transition-all"
                            :class="copied ? 'bg-emerald-900/40 text-emerald-400' : 'bg-slate-700 text-slate-300 hover:bg-slate-600'">
                        <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Details Grid --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Unit Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Type</p>
                    <p class="text-slate-200 mt-0.5">{{ ucfirst($unit->type) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Floor</p>
                    <p class="text-slate-200 mt-0.5">{{ $unit->floor ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Bedrooms</p>
                    <p class="text-slate-200 mt-0.5">{{ $unit->bedrooms ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Bathrooms</p>
                    <p class="text-slate-200 mt-0.5">{{ $unit->bathrooms ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Size (sqm)</p>
                    <p class="text-slate-200 mt-0.5">{{ $unit->size_sqm ? number_format($unit->size_sqm, 1) : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Monthly Rent</p>
                    <p class="text-[#0078D4] font-semibold mt-0.5">R{{ number_format($unit->monthly_rent, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Deposit</p>
                    <p class="text-slate-200 mt-0.5">{{ $unit->deposit_amount ? 'R'.number_format($unit->deposit_amount, 2) : '—' }}</p>
                </div>
                @if($unit->notes)
                <div class="col-span-2">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Notes</p>
                    <p class="text-slate-300 text-sm mt-0.5">{{ $unit->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Active Lease --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Active Lease</h3>
            @if($unit->activeLease)
                @php $lease = $unit->activeLease; @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Renter</p>
                        <p class="text-slate-200 mt-0.5">{{ $lease->renter?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Lease Start</p>
                        <p class="text-slate-200 mt-0.5">{{ \Carbon\Carbon::parse($lease->start_date)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Lease End</p>
                        <p class="text-slate-200 mt-0.5">{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('d M Y') : 'Month-to-month' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Monthly Rent</p>
                        <p class="text-[#0078D4] font-semibold mt-0.5">R{{ number_format($lease->monthly_rent, 2) }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('leases.show', $lease) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0]">View Full Lease &rarr;</a>
                </div>
            @else
                <p class="text-slate-500 text-sm">No active lease. <a href="{{ route('leases.create', ['unit_id' => $unit->id]) }}" class="text-[#0078D4] hover:text-[#B8D4F0]">Create one</a></p>
            @endif
        </div>

        {{-- Unit Reconciliation --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">Unit Reconciliation</h3>
            </div>
            <div class="grid grid-cols-3 divide-x divide-slate-700 border-b border-slate-700">
                <div class="p-4">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Rent Income</p>
                    <p class="text-emerald-400 font-bold text-lg mt-1">R{{ number_format($recon['income'], 2) }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Expenses</p>
                    <p class="text-red-400 font-bold text-lg mt-1">R{{ number_format($recon['expenses'], 2) }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Net</p>
                    <p class="{{ $recon['net'] >= 0 ? 'text-[#0078D4]' : 'text-red-400' }} font-bold text-lg mt-1">R{{ number_format($recon['net'], 2) }}</p>
                </div>
            </div>
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-2 font-medium">Date</th>
                        <th class="px-4 py-2 font-medium">Category</th>
                        <th class="px-4 py-2 font-medium">Description</th>
                        <th class="px-4 py-2 font-medium">Amount</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($unit->expenses as $expense)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 text-slate-300 text-xs">{{ ucfirst($expense->category) }}</td>
                            <td class="px-4 py-2.5 text-slate-200">
                                {{ $expense->description }}
                                @if($expense->maintenance_request_id)
                                    <a href="{{ route('maintenance.show', $expense->maintenance_request_id) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] ml-1">(ticket)</a>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-red-400 font-medium">R{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <form method="POST" action="{{ route('properties.units.expenses.destroy', [$property, $unit, $expense]) }}"
                                      onsubmit="return confirm('Remove this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <form method="POST" action="{{ route('properties.units.expenses.store', [$property, $unit]) }}"
                  class="px-5 py-4 border-t border-slate-700 space-y-3">
                @csrf
                <h4 class="text-xs font-semibold text-slate-400 uppercase">Add Expense</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Category *</label>
                        <select name="category" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            @foreach(['maintenance','rates','insurance','levy','other'] as $c)
                                <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-xs font-medium text-slate-400 mb-1">Description *</label>
                        <input type="text" name="description" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Amount (R) *</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Date *</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">
                        Add Expense
                    </button>
                </div>
            </form>
        </div>

        {{-- Rent Payments --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">Recent Rent Payments</h3>
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
                    @forelse($unit->rentPayments ?? [] as $payment)
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

        {{-- Maintenance Requests --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Open Maintenance Requests</h3>
            @forelse($unit->maintenanceRequests ?? [] as $request)
                <div class="flex items-center justify-between py-2 border-b border-slate-700 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($request->priority === 'urgent') bg-red-900/40 text-red-400
                            @elseif($request->priority === 'high') bg-orange-900/40 text-orange-400
                            @elseif($request->priority === 'medium') bg-yellow-900/40 text-yellow-400
                            @else bg-slate-700 text-slate-400 @endif">
                            {{ ucfirst($request->priority) }}
                        </span>
                        <span class="text-slate-200 text-sm">{{ $request->title }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-xs bg-slate-700 text-slate-400">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</span>
                        <a href="{{ route('maintenance.show', $request) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0]">View</a>
                    </div>
                </div>
            @empty
                <p class="text-slate-500 text-sm">No open maintenance requests.</p>
            @endforelse
        </div>

    </div>
</x-app-layout>
