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
                    // Filter staff to those who can perform at least one of the booked services
                    $serviceIds = $appointment->services->pluck('id')->all();
                    $assignableStaff = \App\Modules\Booking\Models\Staff::where('is_active', true)
                        ->whereHas('services', fn($q) => $q->whereIn('services.id', $serviceIds))
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
            @php
                $colors = [
                    'pending'   => 'yellow',
                    'confirmed' => 'emerald',
                    'completed' => 'blue',
                    'cancelled' => 'red',
                    'no_show'   => 'slate',
                    'tentative' => 'purple',
                    'awaiting_payment' => 'amber',
                ];
                $c = $colors[$appointment->status] ?? 'slate';
            @endphp
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                    </span>
                    @if($appointment->sale)
                        <a href="{{ route('pos.sales.show', $appointment->sale) }}"
                           class="text-sm bg-emerald-800/50 hover:bg-emerald-800 text-emerald-400 border border-emerald-700 px-4 py-2 rounded-lg">
                            View Receipt · {{ $appointment->sale->reference }}
                        </a>
                    @elseif($hasPos && in_array($appointment->status, ['confirmed', 'pending']) && !$appointment->isUnassigned())
                        <a href="{{ route('pos.terminal', ['appointment' => $appointment->id]) }}"
                           class="text-sm bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg font-medium">
                            Checkout →
                        </a>
                    @endif
                </div>
                <a href="{{ route('appointments.edit', $appointment) }}"
                   class="shrink-0 text-sm bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg">Edit</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
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

                {{-- Services --}}
                <div class="col-span-2">
                    @php
                        $comboServiceIds = [];
                        $comboModel      = null;
                        if ($appointment->combo_id) {
                            $comboModel      = \App\Models\ServiceCombo::with('services')->find($appointment->combo_id);
                            $comboServiceIds = $comboModel ? $comboModel->services->pluck('id')->all() : [];
                        }
                        $fullTotal   = $appointment->services->sum(fn($s) => $s->pivot->price_at_booking ?? $s->price);
                        $extrasCost  = $appointment->combo_price
                            ? $appointment->services
                                ->filter(fn($s) => !in_array($s->id, $comboServiceIds))
                                ->sum(fn($s) => $s->pivot->price_at_booking ?? $s->price)
                            : 0;
                        $grandTotal  = $appointment->combo_price
                            ? (float)$appointment->combo_price + $extrasCost
                            : (float)$fullTotal;
                        $grandTotal -= (float)($appointment->promo_discount ?? 0);
                    @endphp
                    @if($appointment->combo_id)
                        <div class="flex items-center gap-2 mb-3 px-3 py-2 rounded-lg bg-indigo-900/40 border border-indigo-800/60">
                            <span class="text-indigo-400">✨</span>
                            <span class="text-xs font-semibold text-indigo-300">{{ $comboModel?->name ?? 'Combo deal' }} applied</span>
                        </div>
                    @endif
                    <p class="text-slate-400 mb-2">Services</p>
                    <div class="divide-y divide-slate-700">
                        @foreach($appointment->services as $service)
                            @php $inCombo = in_array($service->id, $comboServiceIds); @endphp
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-medium">{{ $service->name }}</span>
                                    @if($inCombo)
                                        <span class="text-xs bg-indigo-900/60 text-indigo-300 font-bold px-2 py-0.5 rounded-full border border-indigo-800/60">combo</span>
                                    @endif
                                </div>
                                <span class="text-slate-400 text-xs">
                                    {{ $service->pivot->duration_minutes ?? $service->duration_minutes }} min
                                    @if($inCombo && $appointment->combo_price)
                                        &nbsp;<span class="line-through opacity-40">R{{ number_format($service->pivot->price_at_booking ?? $service->price, 2) }}</span>
                                    @else
                                        &nbsp;· R{{ number_format($service->pivot->price_at_booking ?? $service->price, 2) }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if($appointment->combo_price)
                        <div class="flex items-center justify-between pt-2 border-t border-slate-700 text-xs text-indigo-400 font-semibold">
                            <span>{{ $comboModel?->name ?? 'Combo deal' }}</span>
                            <span>R{{ number_format($appointment->combo_price, 2) }}</span>
                        </div>
                        @if($extrasCost > 0)
                            <div class="flex items-center justify-between pt-1 text-xs text-slate-500">
                                <span>Add-on services</span>
                                <span>R{{ number_format($extrasCost, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between pt-2 border-t border-slate-700 text-xs text-slate-500">
                            <span>Full price</span>
                            <span class="line-through">R{{ number_format($fullTotal, 2) }}</span>
                        </div>
                        @if($appointment->promo_discount)
                            <div class="flex items-center justify-between pt-1 text-xs text-amber-400 font-semibold">
                                <span>Promo ({{ $appointment->promo_code }})</span>
                                <span>–R{{ number_format($appointment->promo_discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between pt-1 text-sm font-semibold">
                            <span class="text-emerald-400">Total</span>
                            <span class="text-white">
                                R{{ number_format($grandTotal, 2) }} · {{ $appointment->duration_minutes }} min
                                <span class="text-emerald-400 text-xs font-normal ml-1">(–R{{ number_format($fullTotal - $grandTotal, 2) }} saved)</span>
                            </span>
                        </div>
                    @else
                        @if($appointment->promo_discount)
                            <div class="flex items-center justify-between pt-2 border-t border-slate-700 text-xs text-slate-500">
                                <span>Full price</span>
                                <span class="line-through">R{{ number_format($fullTotal, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1 text-xs text-amber-400 font-semibold">
                                <span>Promo ({{ $appointment->promo_code }})</span>
                                <span>–R{{ number_format($appointment->promo_discount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1 text-sm font-semibold">
                                <span class="text-emerald-400">Total</span>
                                <span class="text-white">R{{ number_format($grandTotal, 2) }} · {{ $appointment->duration_minutes }} min</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between pt-2 border-t border-slate-700 text-sm font-semibold">
                                <span class="text-slate-400">Total</span>
                                <span class="text-white">R{{ number_format($fullTotal, 2) }} · {{ $appointment->duration_minutes }} min</span>
                            </div>
                        @endif
                    @endif
                </div>

                <div>
                    <p class="text-slate-400">Scheduled</p>
                    <p class="text-white font-medium">{{ $appointment->scheduled_at->format('d M Y, H:i') }}</p>
                    <p class="text-slate-400 text-xs">
                        Until {{ $appointment->scheduled_at->copy()->addMinutes($appointment->duration_minutes)->format('H:i') }}
                    </p>
                </div>
            </div>

            @if($appointment->notes)
                <div class="pt-2 border-t border-slate-700">
                    <p class="text-slate-400 text-sm mb-1">Notes</p>
                    <p class="text-slate-300 text-sm">{{ $appointment->notes }}</p>
                </div>
            @endif

            {{-- Event Brief --}}
            @if($appointment->isEventBooking())
            <div class="pt-3 border-t border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400 mb-3">Event Brief</p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    @if($appointment->headcount)
                    <div>
                        <p class="text-slate-400 text-xs">Guest Count</p>
                        <p class="text-white font-medium">{{ $appointment->headcount }} pax</p>
                    </div>
                    @endif
                    @if($appointment->event_type)
                    <div>
                        <p class="text-slate-400 text-xs">Event Type</p>
                        <p class="text-white font-medium">{{ $appointment->event_type }}</p>
                    </div>
                    @endif
                    @if($appointment->venue)
                    <div class="col-span-2">
                        <p class="text-slate-400 text-xs">Venue</p>
                        <p class="text-white">{{ $appointment->venue }}</p>
                    </div>
                    @endif
                    @if($appointment->setup_at)
                    <div>
                        <p class="text-slate-400 text-xs">Setup Time</p>
                        <p class="text-white">{{ $appointment->setup_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                    @if($appointment->breakdown_at)
                    <div>
                        <p class="text-slate-400 text-xs">Breakdown</p>
                        <p class="text-white">{{ $appointment->breakdown_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                    @if($appointment->dietary_notes)
                    <div class="col-span-2">
                        <p class="text-slate-400 text-xs">Dietary Requirements</p>
                        <p class="text-slate-200 text-sm">{{ $appointment->dietary_notes }}</p>
                    </div>
                    @endif
                    @if($appointment->theme_notes)
                    <div class="col-span-2">
                        <p class="text-slate-400 text-xs">Theme / Style Notes</p>
                        <p class="text-slate-200 text-sm">{{ $appointment->theme_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Actions: status + WhatsApp + reminder + mark paid -->
        @if(!$appointment->sale)
            @php
                // WhatsApp pre-filled message
                $phone = preg_replace('/[^0-9]/', '', $appointment->customer->phone ?? '');
                if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
                    $phone = '27' . substr($phone, 1);
                }
                $apptDate      = $appointment->scheduled_at->format('l, d F Y \a\t H:i');
                $serviceNames  = $appointment->services->pluck('name')->join(', ');
                $waAmount      = 'R' . number_format($grandTotal, 2);
                $waMessage     = "Hi {$appointment->customer->name}, this is a reminder about your appointment on {$apptDate} for {$serviceNames}. An outstanding balance of {$waAmount} is due. Please contact us to arrange payment. Thank you!";
                $whatsappUrl   = $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($waMessage) : null;
            @endphp

            {{-- Mark as paid (awaiting_payment only) --}}
            @if($appointment->status === 'awaiting_payment')
                <div class="bg-amber-900/25 border border-amber-700/50 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-amber-300">Awaiting payment</p>
                        <p class="text-xs text-slate-400 mt-0.5">Service completed — outstanding balance
                            <span class="text-amber-300 font-bold">{{ $waAmount }}</span>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('appointments.mark-paid', $appointment) }}">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-lg transition">
                            Mark as Paid ✓
                        </button>
                    </form>
                </div>
            @endif

            {{-- Send reminder (WhatsApp + in-app/email) --}}
            @if(in_array($appointment->status, ['confirmed', 'pending', 'awaiting_payment']))
                <div class="bg-slate-800 rounded-xl p-4 flex flex-wrap items-center gap-3">
                    <p class="text-sm text-slate-400 flex-1">Send reminder to {{ $appointment->customer->name }}</p>
                    <div class="flex gap-2 flex-wrap">
                        @if($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white text-xs font-bold rounded-lg transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </a>
                        @endif
                        <form method="POST" action="{{ route('appointments.remind', $appointment) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Email + Notify
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Quick status update --}}
            <div class="bg-slate-800 rounded-xl p-4">
                <p class="text-sm text-slate-400 mb-3">Update status</p>
                <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="flex flex-wrap gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="customer_id" value="{{ $appointment->customer_id }}">
                    <input type="hidden" name="staff_id" value="{{ $appointment->staff_id }}">
                    @foreach($appointment->services as $service)
                        <input type="hidden" name="service_ids[]" value="{{ $service->id }}">
                    @endforeach
                    <input type="hidden" name="scheduled_at" value="{{ $appointment->scheduled_at->format('Y-m-d\TH:i') }}">
                    <input type="hidden" name="notes" value="{{ $appointment->notes }}">
                    @php
                        $statuses = ['pending', 'confirmed', 'awaiting_payment', 'cancelled', 'no_show'];
                        if (!$hasPos) $statuses[] = 'completed';
                    @endphp
                    @foreach($statuses as $s)
                        <button type="submit" name="status" value="{{ $s }}"
                                class="text-xs px-3 py-1.5 rounded-lg {{ $appointment->status === $s ? 'bg-indigo-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </button>
                    @endforeach
                </form>
                @if($hasPos)
                    <p class="text-xs text-slate-600 mt-2">Tip: use Checkout to process payment and mark as completed.</p>
                @else
                    <p class="text-xs text-slate-600 mt-2">Use <strong class="text-slate-500">Awaiting Payment</strong> when service is done but not yet paid.</p>
                @endif
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