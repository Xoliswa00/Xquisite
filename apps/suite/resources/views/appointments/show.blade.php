<x-app-layout>
    <x-slot name="header">Booking #{{ $appointment->id }}</x-slot>

    <div class="max-w-2xl space-y-4">

        <!-- Unassigned staff alert -->
        @if($appointment->isUnassigned())
        <div class="bg-orange-900/20 border border-orange-700/50 rounded-xl p-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-orange-400">No staff member assigned</p>
                <p class="text-xs text-slate-400 mt-0.5">Assign a staff member to confirm this booking.</p>
            </div>
        </div>

        <!-- Assign staff form -->
        <div class="bg-slate-800 rounded-xl p-5">
            <p class="text-sm font-medium text-slate-300 mb-3">Assign Staff Member</p>
            <form method="POST" action="{{ route('appointments.assign', $appointment) }}" class="flex gap-3 flex-wrap">
                @csrf
                @if($errors->has('staff_id'))
                    <p class="w-full text-xs text-red-400">{{ $errors->first('staff_id') }}</p>
                @endif
                @php
                    $assignableStaff = \App\Modules\Booking\Models\Staff::where('is_active', true)
                        ->whereHas('services', fn($q) => $q->where('services.id', $appointment->service_id))
                        ->orderBy('name')
                        ->get();
                @endphp
                <select name="staff_id" required
                        class="bg-slate-700 border-slate-600 text-slate-200 rounded-lg text-sm flex-1">
                    <option value="">Select staff member…</option>
                    @foreach($assignableStaff as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}{{ $member->role ? ' — ' . $member->role : '' }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg">
                    Assign &amp; Confirm
                </button>
            </form>
        </div>
        @endif

        <!-- Detail card -->
        <div class="bg-slate-800 rounded-xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appointment->status] ?? 'slate'; @endphp
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                </span>
                <div class="flex items-center gap-2">
                    @if($appointment->sale)
                        <a href="{{ route('pos.sales.show', $appointment->sale) }}"
                           class="text-sm bg-emerald-800/50 hover:bg-emerald-800 text-emerald-400 border border-emerald-700 px-4 py-2 rounded-lg">
                            View Receipt · {{ $appointment->sale->reference }}
                        </a>
                    @elseif(in_array($appointment->status, ['confirmed', 'pending']) && !$appointment->isUnassigned())
                        <a href="{{ route('pos.terminal', ['appointment' => $appointment->id]) }}"
                           class="text-sm bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg font-medium">
                            Checkout →
                        </a>
                    @endif
                    <a href="{{ route('appointments.edit', $appointment) }}"
                       class="text-sm bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg">Edit</a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-400">Customer</p>
                    <a href="{{ route('customers.show', $appointment->customer) }}" class="text-indigo-400 hover:text-indigo-300 font-medium">
                        {{ $appointment->customer->name }}
                    </a>
                    @if($appointment->customer->phone)
                        <p class="text-slate-400 text-xs">{{ $appointment->customer->phone }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-slate-400">Staff</p>
                    @if($appointment->isUnassigned())
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-900/40 text-orange-400 border border-orange-800">Unassigned</span>
                    @else
                        <p class="text-white font-medium">{{ $appointment->staff->name }}</p>
                        <p class="text-slate-400 text-xs">{{ $appointment->staff->role }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-slate-400">Service</p>
                    <p class="text-white font-medium">{{ $appointment->service->name }}</p>
                    <p class="text-slate-400 text-xs">R{{ number_format($appointment->service->price, 2) }} · {{ $appointment->service->duration_minutes }}min</p>
                </div>
                <div>
                    <p class="text-slate-400">Scheduled</p>
                    <p class="text-white font-medium">{{ $appointment->scheduled_at->format('d M Y, H:i') }}</p>
                    <p class="text-slate-400 text-xs">{{ $appointment->duration_minutes }} minutes</p>
                </div>
            </div>

            @if($appointment->notes)
                <div class="pt-2 border-t border-slate-700">
                    <p class="text-slate-400 text-sm mb-1">Notes</p>
                    <p class="text-slate-300 text-sm">{{ $appointment->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Quick status update (hide if checked out) -->
        @if(!$appointment->sale)
            <div class="bg-slate-800 rounded-xl p-4">
                <p class="text-sm text-slate-400 mb-3">Update status</p>
                <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="flex flex-wrap gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="customer_id" value="{{ $appointment->customer_id }}">
                    <input type="hidden" name="staff_id" value="{{ $appointment->staff_id }}">
                    <input type="hidden" name="service_id" value="{{ $appointment->service_id }}">
                    <input type="hidden" name="scheduled_at" value="{{ $appointment->scheduled_at->format('Y-m-d\TH:i') }}">
                    <input type="hidden" name="duration_minutes" value="{{ $appointment->duration_minutes }}">
                    <input type="hidden" name="notes" value="{{ $appointment->notes }}">
                    @foreach(['pending','confirmed','cancelled','no_show'] as $s)
                        <button type="submit" name="status" value="{{ $s }}"
                                class="text-xs px-3 py-1.5 rounded-lg {{ $appointment->status === $s ? 'bg-indigo-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }}">
                            {{ ucfirst(str_replace('_',' ',$s)) }}
                        </button>
                    @endforeach
                </form>
                <p class="text-xs text-slate-600 mt-2">Tip: use the Checkout button to mark as completed and process payment.</p>
                <p class="text-xs text-slate-600 mt-1">Rescheduling (via Edit) will clear the staff assignment — you'll need to re-assign.</p>
            </div>
        @else
            <!-- Sale summary if checked out -->
            <div class="bg-slate-800 rounded-xl p-4 border border-emerald-900/50">
                <p class="text-xs text-slate-400 mb-2">SALE</p>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white font-medium">{{ $appointment->sale->reference }}</p>
                        <p class="text-sm text-slate-400">
                            {{ $appointment->sale->items->count() }} items ·
                            {{ strtoupper($appointment->sale->payment_method) }} ·
                            {{ $appointment->sale->paid_at?->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <p class="text-xl font-bold text-white">R{{ number_format($appointment->sale->total, 2) }}</p>
                </div>
            </div>
        @endif

        <a href="{{ route('appointments.index') }}" class="inline-block text-sm text-slate-400 hover:text-white">
            ← Back to bookings
        </a>
    </div>
</x-app-layout>
