<x-app-layout>
    <x-slot name="header">Charge &mdash; {{ $charge->description }}</x-slot>

    <div class="max-w-3xl mx-auto p-6 space-y-6">

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[#D4AF37]">{{ $charge->description }}</h2>
                    <a href="{{ route('leases.show', $charge->lease) }}" class="text-sm text-slate-400 hover:text-white mt-0.5 inline-block">&larr; Back to Lease #{{ $charge->lease_id }}</a>
                </div>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    @if($charge->status === 'paid') bg-emerald-900/40 text-emerald-400
                    @elseif($charge->status === 'partial') bg-yellow-900/40 text-yellow-400
                    @elseif($charge->status === 'overdue') bg-red-900/40 text-red-400
                    @else bg-slate-700 text-slate-400 @endif">
                    {{ ucfirst($charge->status) }}
                </span>
            </div>
        </div>

        {{-- Details --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Charge Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Type</p>
                    <p class="text-slate-200 mt-0.5">{{ ucfirst(str_replace('_', ' ', $charge->type)) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Renter</p>
                    <p class="text-slate-200 mt-0.5">{{ $charge->lease->renter?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Unit</p>
                    <p class="text-slate-200 mt-0.5">
                        {{ $charge->lease->property?->name ?? '—' }} &mdash; Unit {{ $charge->lease->unit?->unit_number ?? '—' }}
                    </p>
                </div>
                @if($charge->period)
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Period</p>
                    <p class="text-slate-200 mt-0.5">{{ $charge->period }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Amount (Excl.)</p>
                    <p class="text-slate-200 mt-0.5">R{{ number_format($charge->amount_excl, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">VAT ({{ number_format($charge->vat_rate, 0) }}%)</p>
                    <p class="text-slate-200 mt-0.5">R{{ number_format($charge->vat_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Amount Due (Incl.)</p>
                    <p class="text-slate-200 font-semibold mt-0.5">R{{ number_format($charge->amount_incl, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Amount Paid</p>
                    <p class="text-emerald-400 font-semibold mt-0.5">R{{ number_format($charge->amount_paid ?? 0, 2) }}</p>
                </div>
                @if($charge->due_date)
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Due Date</p>
                    <p class="text-slate-200 mt-0.5">{{ \Carbon\Carbon::parse($charge->due_date)->format('d M Y') }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Paid Date</p>
                    <p class="text-slate-200 mt-0.5">{{ $charge->paid_date ? \Carbon\Carbon::parse($charge->paid_date)->format('d M Y') : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Payment Method</p>
                    <p class="text-slate-200 mt-0.5">{{ $charge->payment_method ? ucfirst(str_replace('_', ' ', $charge->payment_method)) : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Reference</p>
                    <p class="text-slate-200 mt-0.5">{{ $charge->reference ?? '—' }}</p>
                </div>
                @if($charge->meter_reading_start !== null || $charge->meter_reading_end !== null)
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Meter Reading</p>
                    <p class="text-slate-200 mt-0.5">{{ $charge->meter_reading_start ?? '—' }} &rarr; {{ $charge->meter_reading_end ?? '—' }}</p>
                </div>
                @endif
                @if($charge->notes)
                <div class="col-span-full">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Notes</p>
                    <p class="text-slate-300 text-sm mt-0.5">{{ $charge->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Record Payment Form --}}
        @if($charge->status !== 'paid')
            <div class="bg-slate-800 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Record Payment</h3>
                <form method="POST" action="{{ route('lease-charges.record', $charge) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    @if($errors->any())
                        <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Amount Paid (R) *</label>
                            <input type="number" name="amount_paid" value="{{ old('amount_paid', $charge->amount_incl - ($charge->amount_paid ?? 0)) }}"
                                   step="0.01" min="0" required
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Payment Date *</label>
                            <input type="date" name="paid_date" value="{{ old('paid_date', date('Y-m-d')) }}" required
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Payment Method *</label>
                            <select name="payment_method" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                                <option value="">Select method...</option>
                                @foreach(['cash','eft','card','debit_order','other'] as $method)
                                    <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Reference / Proof No.</label>
                            <input type="text" name="reference" value="{{ old('reference') }}"
                                   class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-5 py-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold">
                            Record Payment
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
