<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Payment &mdash; {{ $rentPayment->period }}</h2>
                <a href="{{ route('rent-payments.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Back to Payments</a>
            </div>
            <span class="px-2 py-0.5 rounded text-xs font-medium
                @if($rentPayment->status === 'paid') bg-emerald-900/40 text-emerald-400
                @elseif($rentPayment->status === 'partial') bg-yellow-900/40 text-yellow-400
                @elseif($rentPayment->status === 'overdue') bg-red-900/40 text-red-400
                @else bg-slate-700 text-slate-400 @endif">
                {{ ucfirst($rentPayment->status) }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Details --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Payment Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Period</p>
                    <p class="text-slate-200 mt-0.5">{{ $rentPayment->period }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Renter</p>
                    <p class="text-slate-200 mt-0.5">{{ $rentPayment->renter?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Unit</p>
                    <p class="text-slate-200 mt-0.5">
                        {{ $rentPayment->lease?->property?->name ?? '—' }} &mdash; Unit {{ $rentPayment->unit?->unit_number ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Amount Due</p>
                    <p class="text-slate-200 font-semibold mt-0.5">R{{ number_format($rentPayment->amount_due, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Amount Paid</p>
                    <p class="text-emerald-400 font-semibold mt-0.5">R{{ number_format($rentPayment->amount_paid ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Due Date</p>
                    <p class="text-slate-200 mt-0.5">{{ \Carbon\Carbon::parse($rentPayment->due_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Paid Date</p>
                    <p class="text-slate-200 mt-0.5">{{ $rentPayment->paid_date ? \Carbon\Carbon::parse($rentPayment->paid_date)->format('d M Y') : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Payment Method</p>
                    <p class="text-slate-200 mt-0.5">{{ $rentPayment->payment_method ? ucfirst(str_replace('_', ' ', $rentPayment->payment_method)) : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Reference</p>
                    <p class="text-slate-200 mt-0.5">{{ $rentPayment->reference ?? '—' }}</p>
                </div>
                @if($rentPayment->notes)
                <div class="col-span-full">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Notes</p>
                    <p class="text-slate-300 text-sm mt-0.5">{{ $rentPayment->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Record Payment Form --}}
        @if($rentPayment->status !== 'paid')
            <div class="bg-slate-800 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Record Payment</h3>
                <form method="POST" action="{{ route('rent-payments.record', $rentPayment) }}" class="space-y-4">
                    @csrf

                    @if($errors->any())
                        <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Amount Paid (R) *</label>
                            <input type="number" name="amount_paid" value="{{ old('amount_paid', $rentPayment->amount_due - ($rentPayment->amount_paid ?? 0)) }}"
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
                            <label class="block text-xs font-medium text-slate-400 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                                <option value="">Select method...</option>
                                @foreach(['cash','eft','bank_transfer','card','cheque','other'] as $method)
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

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                                  class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">{{ old('notes') }}</textarea>
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
