<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rental-orders.index') }}" class="text-slate-400 hover:text-white">← Rentals</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-xl font-semibold">{{ $rentalOrder->reference }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl py-8 px-4 sm:px-6 lg:px-8 space-y-5">

        @if (session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        {{-- Detail card --}}
        <div class="bg-slate-800 rounded-xl p-6 space-y-4">
            @php
                $statusColours = [
                    'reserved' => 'bg-blue-900/40 text-blue-400 border-blue-800',
                    'out'      => 'bg-amber-900/40 text-amber-400 border-amber-800',
                    'returned' => 'bg-emerald-900/40 text-emerald-400 border-emerald-800',
                    'overdue'  => 'bg-red-900/40 text-red-400 border-red-800',
                    'damaged'  => 'bg-red-900/60 text-red-300 border-red-700',
                ];
            @endphp

            <div class="flex items-center justify-between">
                <span class="text-sm px-3 py-1 rounded-full border {{ $statusColours[$rentalOrder->status] ?? '' }}">
                    {{ ucfirst($rentalOrder->status) }}
                </span>
                <span class="text-xl font-bold text-slate-100">R{{ number_format($rentalOrder->totalCharge(), 2) }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-400 text-xs">Item</p>
                    <p class="text-white font-medium">{{ $rentalOrder->product->name }}</p>
                    <p class="text-slate-400 text-xs">R{{ number_format($rentalOrder->rental_rate, 2) }}/event × {{ $rentalOrder->quantity }} unit(s)</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs">Customer</p>
                    <p class="text-white">{{ $rentalOrder->customer?->name ?? 'Walk-in' }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs">Event Date</p>
                    <p class="text-white font-medium">{{ $rentalOrder->event_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs">Return By</p>
                    <p class="font-medium {{ $rentalOrder->isOverdue() ? 'text-red-400' : 'text-white' }}">
                        {{ $rentalOrder->return_due_at->format('d M Y') }}
                        @if ($rentalOrder->isOverdue()) <span class="text-xs">(OVERDUE)</span> @endif
                    </p>
                </div>
                @if ($rentalOrder->returned_at)
                <div>
                    <p class="text-slate-400 text-xs">Returned</p>
                    <p class="text-white">{{ $rentalOrder->returned_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs">Condition on Return</p>
                    <p class="text-white">{{ ucfirst($rentalOrder->condition_on_return) }}</p>
                </div>
                @endif
                @if ($rentalOrder->notes)
                <div class="col-span-2">
                    <p class="text-slate-400 text-xs">Notes</p>
                    <p class="text-slate-200 text-sm">{{ $rentalOrder->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        @if ($rentalOrder->status === 'reserved')
        <div class="bg-slate-800 rounded-xl p-5">
            <p class="text-sm font-medium text-slate-300 mb-3">Mark items as sent out for event</p>
            <form method="POST" action="{{ route('rental-orders.out', $rentalOrder) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-sm rounded-lg font-medium">
                    Mark as Out
                </button>
            </form>
        </div>
        @endif

        @if (in_array($rentalOrder->status, ['out', 'overdue']))
        <div class="bg-slate-800 rounded-xl p-5">
            <p class="text-sm font-medium text-slate-300 mb-3">Record return</p>
            <form method="POST" action="{{ route('rental-orders.return', $rentalOrder) }}" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                @csrf @method('PATCH')
                <select name="condition_on_return" required
                        class="bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">— Condition on return —</option>
                    <option value="excellent">Excellent — no issues</option>
                    <option value="good">Good — minor wear</option>
                    <option value="fair">Fair — needs cleaning</option>
                    <option value="damaged">Damaged — follow up required</option>
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm rounded-lg font-medium">
                    Record Return
                </button>
            </form>
        </div>
        @endif

    </div>
</x-app-layout>
