<x-app-layout>
    <x-slot name="header">Lease #{{ $lease->id }}</x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-[#D4AF37]">Lease #{{ $lease->id }}</h2>
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @if($lease->status === 'active') bg-emerald-900/40 text-emerald-400
                        @elseif($lease->status === 'pending') bg-yellow-900/40 text-yellow-400
                        @elseif($lease->status === 'terminated') bg-red-900/40 text-red-400
                        @else bg-slate-700 text-slate-400 @endif">
                        {{ ucfirst($lease->status) }}
                    </span>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('leases.index') }}" class="text-sm text-slate-400 hover:text-white self-center">&larr; Leases</a>
                    <a href="{{ route('leases.agreement', $lease) }}"
                       class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Download Lease Agreement</a>
                    <a href="{{ route('leases.statement', $lease) }}"
                       class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Download Statement</a>
                    @if($lease->status === 'pending')
                        <a href="{{ route('leases.edit', $lease) }}"
                           class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
                    @endif
                </div>
            </div>
        </div>

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
                    <p class="text-[#0078D4] font-semibold mt-0.5">R{{ number_format($lease->monthly_rent, 2) }}</p>
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

        {{-- Inspections --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Inspections</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(['move_in' => 'Move-In', 'move_out' => 'Move-Out'] as $type => $label)
                    @php $existing = $lease->inspections->firstWhere('type', $type); @endphp
                    <div class="flex items-center justify-between p-3 bg-slate-900 rounded-lg">
                        <div>
                            <p class="text-sm text-slate-200 font-medium">{{ $label }} Inspection</p>
                            @if($existing)
                                <span class="text-xs {{ $existing->status === 'completed' ? 'text-emerald-400' : 'text-yellow-400' }}">
                                    {{ $existing->status === 'completed' ? 'Completed' : 'In progress' }}
                                </span>
                            @else
                                <span class="text-xs text-slate-500">Not started</span>
                            @endif
                        </div>
                        <a href="{{ route('leases.inspections.create', [$lease, $type]) }}"
                           class="text-xs {{ $existing ? 'text-[#0078D4] hover:text-[#B8D4F0]' : 'px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg' }}">
                            {{ $existing ? 'View' : 'Start' }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Renew Lease --}}
        @if($lease->status === 'active')
            @php
                $increasePct = $lease->property?->annual_increase_percentage;
                $increaseSource = $increasePct !== null ? "the property's configured rate" : 'the default rate';
                $increasePct = $increasePct !== null ? (float) $increasePct : 10.0;
                $suggestedRent = round((float) $lease->monthly_rent * (1 + $increasePct / 100), 2);
            @endphp
            <div class="bg-slate-800 rounded-xl p-6" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-300">Renew Lease</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Current end date: {{ $lease->end_date?->format('d M Y') ?? 'None (month-to-month)' }}
                        </p>
                    </div>
                    <button type="button" @click="open = !open"
                            class="px-3 py-1.5 bg-emerald-900/40 hover:bg-emerald-800/50 text-emerald-400 text-xs rounded-lg border border-emerald-800">
                        Renew Lease
                    </button>
                </div>
                <div x-show="open" x-transition class="mt-4 pt-4 border-t border-slate-700">
                    <form method="POST" action="{{ route('leases.renew', $lease) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        @if($errors->any())
                            <div class="p-3 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                                <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">New End Date *</label>
                                <input type="date" name="new_end_date" required
                                       value="{{ old('new_end_date', $lease->end_date?->copy()->addYear()->toDateString()) }}"
                                       class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">New Monthly Rent (R)</label>
                                <input type="number" name="new_monthly_rent" step="0.01" min="0"
                                       value="{{ old('new_monthly_rent', $suggestedRent) }}"
                                       class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                                <p class="text-xs text-slate-500 mt-1">
                                    Suggested: R{{ number_format($suggestedRent, 2) }} &mdash; {{ rtrim(rtrim(number_format($increasePct, 2), '0'), '.') }}% increase, using {{ $increaseSource }}. Adjust if needed.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit"
                                    class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm rounded-lg font-medium">
                                Confirm Renewal
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

        {{-- Deposit Reconciliation --}}
        @if($lease->status === 'terminated' && $lease->deposit_amount > 0)
            @php
                $deductionsTotal = $lease->depositDeductions->sum('amount');
                $outstanding = $lease->outstandingBalance();
                $availableRefund = $lease->availableDepositRefund();
                $shortfall = min(0, $lease->deposit_amount - $deductionsTotal - $outstanding);
            @endphp
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-300">Deposit Reconciliation</h3>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-700 border-b border-slate-700">
                    <div class="p-4">
                        <p class="text-xs text-slate-400 uppercase font-semibold">Deposit Held</p>
                        <p class="text-slate-200 font-bold text-lg mt-1">R{{ number_format($lease->deposit_amount, 2) }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-slate-400 uppercase font-semibold">Deductions</p>
                        <p class="text-red-400 font-bold text-lg mt-1">R{{ number_format($deductionsTotal, 2) }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-slate-400 uppercase font-semibold">Outstanding Balance</p>
                        <p class="text-red-400 font-bold text-lg mt-1">R{{ number_format($outstanding, 2) }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-slate-400 uppercase font-semibold">{{ $lease->deposit_status === 'refunded' ? 'Refunded' : 'Available to Refund' }}</p>
                        <p class="text-[#0078D4] font-bold text-lg mt-1">
                            R{{ number_format($lease->deposit_status === 'refunded' ? $lease->deposit_refund_amount : $availableRefund, 2) }}
                        </p>
                    </div>
                </div>

                @if($shortfall < 0)
                    <div class="mx-5 mt-4 p-3 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                        Deposit is fully exhausted — the tenant still owes R{{ number_format(abs($shortfall), 2) }} beyond the deposit.
                    </div>
                @endif

                @if($lease->deposit_status === 'refunded')
                    <div class="p-5 text-sm text-slate-300">
                        Refunded R{{ number_format($lease->deposit_refund_amount, 2) }} via {{ ucfirst(str_replace('_', ' ', $lease->deposit_refund_method)) }}
                        on {{ \Carbon\Carbon::parse($lease->deposit_refund_date)->format('d M Y') }}.
                    </div>
                @else
                    <table class="w-full text-sm summary-on-mobile">
                        <thead>
                            <tr class="border-b border-slate-700 text-slate-400 text-left">
                                <th class="px-4 py-2 font-medium">Description</th>
                                <th class="px-4 py-2 font-medium">Amount</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse($lease->depositDeductions as $deduction)
                                <tr class="hover:bg-slate-700/30">
                                    <td class="px-4 py-2.5 text-slate-200">{{ $deduction->description }}</td>
                                    <td class="px-4 py-2.5 text-red-400 font-medium">R{{ number_format($deduction->amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <form method="POST" action="{{ route('leases.deposit.deductions.destroy', [$lease, $deduction]) }}"
                                              onsubmit="return confirm('Remove this deduction?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 text-xs">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No deductions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <form method="POST" action="{{ route('leases.deposit.deductions.store', $lease) }}"
                          class="px-5 py-4 border-t border-slate-700 flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Deduction Description</label>
                            <input type="text" name="description" required placeholder="e.g. Damage to kitchen counter"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Amount (R)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" required
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">
                            Add Deduction
                        </button>
                    </form>

                    <form method="POST" action="{{ route('leases.deposit.refund', $lease) }}"
                          class="px-5 py-4 border-t border-slate-700 space-y-3">
                        @csrf
                        <h4 class="text-xs font-semibold text-slate-400 uppercase">Finalize Refund</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Refund Amount (R) *</label>
                                <input type="number" name="deposit_refund_amount" step="0.01" min="0" required
                                       value="{{ number_format($availableRefund, 2, '.', '') }}"
                                       class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Refund Date *</label>
                                <input type="date" name="deposit_refund_date" value="{{ date('Y-m-d') }}" required
                                       class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Method *</label>
                                <select name="deposit_refund_method" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                                    <option value="">Select...</option>
                                    @foreach(['eft','cash','card','debit_order','other'] as $method)
                                        <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                        class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm rounded-lg font-medium w-full"
                                        onclick="return confirm('Finalize the deposit refund? This cannot be undone.')">
                                    Finalize Refund
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        @endif

        {{-- Charges & Statement --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-300">Charges</h3>
                <p class="text-xs text-slate-400">
                    Outstanding: <span class="text-red-400 font-semibold">R{{ number_format($lease->charges->sum(fn($c) => $c->amount_incl - $c->amount_paid), 2) }}</span>
                </p>
            </div>
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-2 font-medium">Date</th>
                        <th class="px-4 py-2 font-medium">Type</th>
                        <th class="px-4 py-2 font-medium">Description</th>
                        <th class="px-4 py-2 font-medium">Excl.</th>
                        <th class="px-4 py-2 font-medium">VAT</th>
                        <th class="px-4 py-2 font-medium">Incl.</th>
                        <th class="px-4 py-2 font-medium">Paid</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($lease->charges as $charge)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 text-slate-400 text-xs">{{ $charge->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 text-slate-300 text-xs">{{ ucfirst(str_replace('_', ' ', $charge->type)) }}</td>
                            <td class="px-4 py-2.5 text-slate-200">{{ $charge->description }}</td>
                            <td class="px-4 py-2.5 text-slate-300">R{{ number_format($charge->amount_excl, 2) }}</td>
                            <td class="px-4 py-2.5 text-slate-400 text-xs">R{{ number_format($charge->vat_amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-slate-200 font-medium">R{{ number_format($charge->amount_incl, 2) }}</td>
                            <td class="px-4 py-2.5 text-emerald-400">R{{ number_format($charge->amount_paid, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($charge->status === 'paid') bg-emerald-900/40 text-emerald-400
                                    @elseif($charge->status === 'partial') bg-yellow-900/40 text-yellow-400
                                    @elseif($charge->status === 'overdue') bg-red-900/40 text-red-400
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ ucfirst($charge->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                @if($charge->status !== 'paid')
                                    <a href="{{ route('lease-charges.show', $charge) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">Record</a>
                                @endif
                                @if($charge->amount_paid == 0)
                                    <form method="POST" action="{{ route('leases.charges.destroy', [$lease, $charge]) }}" class="inline"
                                          onsubmit="return confirm('Remove this charge?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-xs ml-2">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">No charges yet.</td></tr>
                    @endforelse
                </tbody>
            </table>

            @php
                $lastReadings = collect(['water', 'electricity'])->mapWithKeys(fn ($t) => [
                    $t => $lease->charges->firstWhere(fn ($c) => $c->type === $t && $c->meter_reading_end !== null)?->meter_reading_end,
                ]);
            @endphp
            <div class="px-5 py-4 border-t border-slate-700"
                 x-data="{
                    type: '',
                    meterStart: '',
                    meterEnd: '',
                    ratePerUnit: '',
                    amountExcl: '{{ old('amount_excl') }}',
                    lastReadings: {{ $lastReadings->toJson() }},
                    get metered() { return this.type === 'water' || this.type === 'electricity'; },
                    get consumption() { return (this.meterEnd !== '' && this.meterStart !== '') ? Math.max(0, this.meterEnd - this.meterStart) : null; },
                    get calculatedAmount() { return (this.consumption !== null && this.ratePerUnit !== '') ? (this.consumption * this.ratePerUnit).toFixed(2) : null; },
                    onTypeChange() { if (this.metered && this.lastReadings[this.type] !== null) { this.meterStart = this.lastReadings[this.type]; } },
                    syncCalculated() { if (this.metered && this.calculatedAmount !== null) { this.amountExcl = this.calculatedAmount; } }
                 }">
                <h4 class="text-xs font-semibold text-slate-400 uppercase mb-3">Add Charge</h4>
                <form method="POST" action="{{ route('leases.charges.store', $lease) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Type *</label>
                            <select name="type" x-model="type" @change="onTypeChange()" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                                <option value="">Select...</option>
                                @foreach(['water','electricity','sewerage','levy','late_fee','discount','deposit','other'] as $t)
                                    <option value="{{ $t }}">{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Description *</label>
                            <input type="text" name="description" required placeholder="e.g. Water Levy Recovery" value="{{ old('description') }}"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Period</label>
                            <input type="text" name="period" placeholder="YYYY-MM" maxlength="7" value="{{ old('period') }}"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date') }}"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3" x-show="metered">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Meter Start</label>
                            <input type="number" name="meter_reading_start" step="0.01" min="0" x-model="meterStart" @input="syncCalculated()"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Meter End</label>
                            <input type="number" name="meter_reading_end" step="0.01" min="0" x-model="meterEnd" @input="syncCalculated()"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Rate per Unit (R)</label>
                            <input type="number" name="rate_per_unit" step="0.0001" min="0" x-model="ratePerUnit" @input="syncCalculated()"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Consumption</label>
                            <p class="text-slate-300 text-sm px-3 py-2" x-text="consumption !== null ? consumption + ' units' : '—'"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Amount Excl. (R) *</label>
                            <input type="number" name="amount_excl" step="0.01" min="0" required x-model="amountExcl"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            <p x-show="type === 'discount'" class="text-xs text-slate-500 mt-1">Enter as a positive amount — applied as a credit automatically.</p>
                            <p x-show="metered && calculatedAmount !== null" class="text-xs text-slate-500 mt-1">Auto-filled from consumption &times; rate — adjust if needed.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">VAT Rate (%)</label>
                            <input type="number" name="vat_rate" step="0.01" min="0" max="100" value="{{ old('vat_rate', 0) }}"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">
                            Add Charge
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
                                       class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">Record</a>
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
