<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-[#D4AF37]">{{ $serviceAgreement->plan_name }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    @if($serviceAgreement->status === 'active') bg-emerald-900/40 text-emerald-400
                    @elseif($serviceAgreement->status === 'pending') bg-yellow-900/40 text-yellow-400
                    @elseif($serviceAgreement->status === 'suspended') bg-orange-900/40 text-orange-400
                    @else bg-red-900/40 text-red-400 @endif">
                    {{ ucfirst($serviceAgreement->status) }}
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('service-agreements.index') }}" class="text-sm text-ink-muted hover:text-ink self-center">&larr; Agreements</a>
                <a href="{{ route('service-agreements.contract-pdf', $serviceAgreement) }}" target="_blank"
                   class="px-3 py-2 bg-panel-2 hover:bg-line-2 text-ink text-sm rounded-lg">Download Contract</a>
                <a href="{{ route('service-agreements.edit', $serviceAgreement) }}"
                   class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Info cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Client</p>
                <p class="text-ink font-medium">{{ $serviceAgreement->client?->name ?? '—' }}</p>
                <p class="text-ink-faint text-xs mt-0.5">{{ $serviceAgreement->client?->email }}</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Monthly Fee</p>
                <p class="text-[#0078D4] font-semibold">R{{ number_format($serviceAgreement->monthly_fee, 2) }}</p>
                <p class="text-ink-faint text-xs mt-0.5">Billed day {{ $serviceAgreement->billing_day }}</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Minutes This Month</p>
                <p class="text-ink font-medium">{{ $serviceAgreement->minutesRemaining() }} / {{ $serviceAgreement->minutes_allowance }} left</p>
                <p class="text-ink-faint text-xs mt-0.5">Unused time doesn't roll over</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Commitment Ends</p>
                <p class="text-ink font-medium">{{ $serviceAgreement->commitmentEndDate()->format('d M Y') }}</p>
                <p class="text-ink-faint text-xs mt-0.5">{{ $serviceAgreement->commitment_months }} months from {{ $serviceAgreement->start_date->format('d M Y') }}</p>
            </div>
        </div>

        {{-- Acceptance --}}
        <div class="bg-panel-2 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-ink-muted mb-3">Client Acceptance</h3>
            @if($serviceAgreement->accepted_at)
                <p class="text-sm text-emerald-400">Accepted by <strong>{{ $serviceAgreement->accepted_name }}</strong> on {{ $serviceAgreement->accepted_at->format('d M Y') }}.</p>
            @else
                <form method="POST" action="{{ route('service-agreements.accept', $serviceAgreement) }}" class="flex items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Client Name</label>
                        <input type="text" name="accepted_name" required class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Date</label>
                        <input type="date" name="accepted_at" value="{{ date('Y-m-d') }}" required class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">Record Acceptance</button>
                </form>
                <p class="text-xs text-ink-faint mt-2">Recorded manually once the client has signed off on the contract — see Download Contract above.</p>
            @endif
        </div>

        {{-- Log a minute-cap change --}}
        @if($serviceAgreement->status === 'active')
            <div class="bg-panel-2 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-ink-muted mb-3">Log a Minor Change</h3>
                <form method="POST" action="{{ route('service-agreements.log-minutes', $serviceAgreement) }}" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-ink-faint mb-1">Description</label>
                        <input type="text" name="description" required placeholder="e.g. Updated contact address"
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div class="w-28">
                        <label class="block text-xs font-medium text-ink-faint mb-1">Minutes</label>
                        <input type="number" name="minutes_used" min="1" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-panel hover:bg-line-2 text-ink text-sm rounded-lg border border-line-2">Log</button>
                </form>

                @if($serviceAgreement->changes->count())
                    <ul class="mt-4 space-y-1.5 pt-4 border-t border-line-2">
                        @foreach($serviceAgreement->changes->take(8) as $change)
                            <li class="text-xs text-ink-muted flex justify-between">
                                <span>{{ $change->description }} <span class="text-ink-faint">({{ $change->period }})</span></span>
                                <span class="text-ink-faint">{{ $change->minutes_used }} min</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        {{-- Charges --}}
        <div class="bg-panel-2 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-line-2">
                <h3 class="text-sm font-semibold text-ink-muted">Monthly Charges</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line-2 text-ink-muted text-left">
                        <th class="px-4 py-2 font-medium">Period</th>
                        <th class="px-4 py-2 font-medium">Amount Due</th>
                        <th class="px-4 py-2 font-medium">Amount Paid</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Due Date</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-2" x-data="{ openCharge: null }">
                    @forelse($serviceAgreement->charges as $charge)
                        <tr class="hover:bg-line-2/40">
                            <td class="px-4 py-2.5 text-ink">{{ $charge->periodLabel() }}</td>
                            <td class="px-4 py-2.5 text-ink-muted">R{{ number_format($charge->amount_due, 2) }}</td>
                            <td class="px-4 py-2.5 text-ink-muted">R{{ number_format($charge->amount_paid, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($charge->status === 'paid') bg-emerald-900/40 text-emerald-400
                                    @elseif($charge->status === 'partial') bg-yellow-900/40 text-yellow-400
                                    @elseif($charge->status === 'overdue') bg-red-900/40 text-red-400
                                    @else bg-panel text-ink-muted @endif">
                                    {{ ucfirst($charge->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-ink-faint text-xs">{{ $charge->due_date->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 text-right">
                                @if($charge->status !== 'paid')
                                    <button type="button" @click="openCharge = openCharge === {{ $charge->id }} ? null : {{ $charge->id }}"
                                            class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">Record</button>
                                @endif
                            </td>
                        </tr>
                        @if($charge->status !== 'paid')
                            <tr x-show="openCharge === {{ $charge->id }}" x-transition>
                                <td colspan="6" class="px-4 pb-4 bg-panel/40">
                                    <form method="POST" action="{{ route('service-agreements.charges.record', [$serviceAgreement, $charge]) }}" class="flex items-end gap-3 pt-2">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="block text-xs font-medium text-ink-faint mb-1">Amount Paid</label>
                                            <input type="number" name="amount_paid" step="0.01" min="0.01" value="{{ $charge->amount_due }}" required
                                                   class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2 w-32">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink-faint mb-1">Paid Date</label>
                                            <input type="date" name="paid_date" value="{{ date('Y-m-d') }}" required
                                                   class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink-faint mb-1">Method</label>
                                            <select name="payment_method" required class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                                                <option value="eft">EFT</option>
                                                <option value="card">Card</option>
                                                <option value="cash">Cash</option>
                                                <option value="debit_order">Debit Order</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-ink-faint mb-1">Reference</label>
                                            <input type="text" name="reference" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                                        </div>
                                        <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-ink-faint">No charges yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Status actions --}}
        <div class="bg-panel-2 rounded-xl p-6 flex flex-wrap gap-3">
            @if($serviceAgreement->status === 'active')
                <form method="POST" action="{{ route('service-agreements.suspend', $serviceAgreement) }}"
                      onsubmit="return confirm('Suspend this agreement?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-orange-900/40 hover:bg-orange-800/50 text-orange-400 text-sm rounded-lg border border-orange-800">Suspend</button>
                </form>
            @elseif($serviceAgreement->status === 'suspended')
                <form method="POST" action="{{ route('service-agreements.reactivate', $serviceAgreement) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-900/40 hover:bg-emerald-800/50 text-emerald-400 text-sm rounded-lg border border-emerald-800">
                        Reactivate (R{{ number_format($serviceAgreement->reactivation_fee, 2) }} fee)
                    </button>
                </form>
            @endif

            @if(!in_array($serviceAgreement->status, ['terminated']))
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="px-4 py-2 bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm rounded-lg border border-red-800">Terminate</button>
                    <div x-show="open" x-transition class="mt-3">
                        <form method="POST" action="{{ route('service-agreements.terminate', $serviceAgreement) }}" class="flex items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-ink-faint mb-1">Date</label>
                                <input type="date" name="terminated_at" value="{{ date('Y-m-d') }}" required class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-ink-faint mb-1">Reason</label>
                                <input type="text" name="termination_reason" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                            </div>
                            <button type="submit" onclick="return confirm('Terminate this agreement?')"
                                    class="px-4 py-2 bg-red-700 hover:bg-red-600 text-white text-sm rounded-lg font-medium">Confirm</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
