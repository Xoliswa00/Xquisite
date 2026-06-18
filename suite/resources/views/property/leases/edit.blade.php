<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Edit Lease #{{ $lease->id }}</h2>
                <p class="text-sm text-slate-400 mt-0.5">{{ $lease->property?->name }} &mdash; Unit {{ $lease->unit?->unit_number }} &mdash; {{ $lease->renter?->name }}</p>
            </div>
            <a href="{{ route('leases.show', $lease) }}" class="text-sm text-slate-400 hover:text-white">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6">

        @if($lease->status !== 'pending')
            <div class="p-4 bg-yellow-900/30 border border-yellow-700 text-yellow-300 rounded-xl text-sm mb-6">
                Only pending leases can be edited. This lease is currently <strong>{{ ucfirst($lease->status) }}</strong>.
            </div>
        @endif

        <form method="POST" action="{{ route('leases.update', $lease) }}" class="space-y-6">
            @csrf @method('PUT')

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Lease Info <span class="text-slate-500 font-normal">(read-only)</span></h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Property</p>
                        <p class="text-slate-300 text-sm mt-0.5">{{ $lease->property?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Unit</p>
                        <p class="text-slate-300 text-sm mt-0.5">{{ $lease->unit?->unit_number ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Renter</p>
                        <p class="text-slate-300 text-sm mt-0.5">{{ $lease->renter?->name ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Lease Period</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Start Date</label>
                        <div class="w-full bg-slate-700/50 border border-slate-600 text-slate-400 rounded-lg text-sm px-3 py-2">
                            {{ \Carbon\Carbon::parse($lease->start_date)->format('d M Y') }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">End Date <span class="text-slate-500">(optional)</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', $lease->end_date) }}"
                               @if($lease->status !== 'pending') disabled @endif
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 disabled:opacity-50">
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Financials</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Monthly Rent (R)</label>
                        <input type="number" name="monthly_rent" value="{{ old('monthly_rent', $lease->monthly_rent) }}" step="0.01" min="0"
                               @if($lease->status !== 'pending') disabled @endif
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 disabled:opacity-50">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Deposit Amount (R)</label>
                        <input type="number" name="deposit_amount" value="{{ old('deposit_amount', $lease->deposit_amount) }}" step="0.01" min="0"
                               @if($lease->status !== 'pending') disabled @endif
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 disabled:opacity-50">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer {{ $lease->status !== 'pending' ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="checkbox" name="deposit_paid" value="1"
                               @checked(old('deposit_paid', $lease->deposit_paid))
                               @if($lease->status !== 'pending') disabled @endif
                               class="rounded border-slate-600 bg-slate-700">
                        <span class="text-sm text-slate-300">Deposit paid</span>
                    </label>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Notes</h3>
                <textarea name="notes" rows="3"
                          @if($lease->status !== 'pending') disabled @endif
                          class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2 disabled:opacity-50">{{ old('notes', $lease->notes) }}</textarea>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('leases.show', $lease) }}"
                   class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">Cancel</a>
                @if($lease->status === 'pending')
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg text-sm font-semibold">
                        Save Changes
                    </button>
                @endif
            </div>
        </form>
    </div>
</x-app-layout>
