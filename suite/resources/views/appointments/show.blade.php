<x-app-layout>
    <x-slot name="header">Booking #{{ $appointment->id }}</x-slot>

    <div class="max-w-2xl space-y-4">

        @if(session('conflict_warning'))
        <div class="bg-amber-900/20 border border-amber-600/50 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-400">Schedule overlap — booking saved</p>
                <p class="text-xs text-slate-300 mt-0.5">{{ session('conflict_warning') }}</p>
            </div>
        </div>
        @endif

        <!-- Unassigned staff alert -->
        @if($appointment->isUnassigned())
        <div class="bg-orange-900/20 border border-orange-700/50 rounded-xl p-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-orange-400">No staff member assigned</p>
                <p class="text-xs text-slate-400 mt-0.5">Assign a staff member to confirm this booking.</p>
            </div>
        </div>

        <!-- Assign staff form -->
        <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg shadow-orange-950/30 border border-orange-800/20">
            <div class="px-5 py-3 border-b border-orange-800/20 bg-gradient-to-r from-orange-500/8 to-transparent flex items-center gap-2">
                <div class="w-5 h-5 rounded-md bg-orange-500/20 border border-orange-500/25 flex items-center justify-center">
                    <svg class="w-3 h-3 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <p class="text-sm font-semibold text-white">Assign Staff Member</p>
            </div>
            <div class="p-5">
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
                        class="bg-slate-700 border border-slate-600 text-slate-200 rounded-lg text-sm flex-1">
                    <option value="">Select staff member…</option>
                    @foreach($assignableStaff as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}{{ $member->role ? ' — ' . $member->role : '' }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-semibold rounded-lg">
                    Assign &amp; Confirm
                </button>
            </form>
            </div>{{-- /p-5 --}}
        </div>
        @endif

        <!-- Detail card -->
        <div class="bg-slate-800 rounded-xl overflow-hidden shadow-xl shadow-black/30 border border-slate-700/50">
            @php
                $colors = [
                    'pending'   => 'yellow',
                    'confirmed' => 'emerald',
                    'completed' => 'blue',
                    'cancelled' => 'red',
                    'no_show'   => 'slate',
                    'tentative' => 'amber',
                    'awaiting_payment' => 'amber',
                ];
                $c = $colors[$appointment->status] ?? 'slate';
            @endphp
            {{-- Card header band --}}
            <div class="px-6 py-4 border-b border-slate-700 bg-gradient-to-r from-{{ $c }}-900/20 to-transparent flex items-center justify-between gap-2 flex-wrap">
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
            </div>{{-- /header band --}}

            <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-400">Customer</p>
                    <a href="{{ route('customers.show', $appointment->customer) }}" class="text-[#0078D4] hover:text-[#B8D4F0] font-medium">
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
                        <div class="flex items-center gap-2 mb-3 px-3 py-2 rounded-lg bg-[#001A3A]/40 border border-[#002B5B]/60">
                            <span class="text-[#0078D4]">✨</span>
                            <span class="text-xs font-semibold text-[#B8D4F0]">{{ $comboModel?->name ?? 'Combo deal' }} applied</span>
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
                                        <span class="text-xs bg-[#001A3A]/60 text-[#B8D4F0] font-bold px-2 py-0.5 rounded-full border border-[#002B5B]/60">combo</span>
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
                        <div class="flex items-center justify-between pt-2 border-t border-slate-700 text-xs text-[#0078D4] font-semibold">
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
                        <span class="text-slate-600">({{ $appointment->duration_minutes }}min booked)</span>
                    </p>
                </div>

                {{-- Actual Duration --}}
                @php
                    $actualMins = $appointment->actual_duration_minutes;
                    $overrun    = $actualMins ? ($actualMins - $appointment->duration_minutes) : null;
                    $fmtActual  = $actualMins
                        ? (($h = floor($actualMins / 60)) > 0 ? "{$h}h " : '') . (($m = $actualMins % 60) > 0 ? "{$m}m" : ($h > 0 ? '' : '0m'))
                        : null;
                @endphp
                <div class="col-span-2 pt-3 border-t border-slate-700/60">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div>
                            <p class="text-slate-400 text-sm">Actual Duration</p>
                            @if($actualMins)
                                <p class="text-white font-medium mt-0.5">
                                    {{ $fmtActual }}
                                    @if($overrun > 0)
                                        <span class="ml-2 inline-flex items-center gap-1 text-xs font-semibold text-amber-400 bg-amber-900/30 border border-amber-800/50 rounded-full px-2.5 py-0.5">
                                            ⏱ {{ $overrun }}min over
                                        </span>
                                    @elseif($overrun < 0)
                                        <span class="ml-2 inline-flex items-center gap-1 text-xs font-semibold text-emerald-400 bg-emerald-900/30 border border-emerald-800/50 rounded-full px-2.5 py-0.5">
                                            ✓ {{ abs($overrun) }}min early
                                        </span>
                                    @endif
                                </p>
                            @else
                                <p class="text-slate-500 text-sm italic mt-0.5">Not recorded yet</p>
                            @endif
                        </div>

                        @if(!in_array($appointment->status, ['cancelled', 'no_show']))
                        <form method="POST" action="{{ route('appointments.actual-duration', $appointment) }}"
                              class="flex items-center gap-2 shrink-0">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="actual_duration_minutes"
                                   value="{{ $actualMins ?? $appointment->duration_minutes }}"
                                   min="1" max="1440"
                                   class="w-24 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-1.5 text-center focus:outline-none focus:border-[#0078D4]">
                            <span class="text-xs text-slate-500">min</span>
                            <button type="submit"
                                    class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 border border-slate-600 rounded-lg font-medium transition-colors">
                                Save
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Proof of payment --}}
            @if($appointment->payment_proof_path)
                <div class="pt-3 border-t border-slate-700">
                    <p class="text-slate-400 text-sm mb-2">Proof of Payment</p>
                    <a href="{{ Storage::disk('public')->url($appointment->payment_proof_path) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 border border-slate-600 text-slate-200 text-xs font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ $appointment->payment_proof_name ?? 'View proof' }}
                    </a>
                    @php
                        $ext = pathinfo($appointment->payment_proof_name ?? '', PATHINFO_EXTENSION);
                    @endphp
                    @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                        <div class="mt-2">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($appointment->payment_proof_path) }}"
                                 alt="Proof of payment"
                                 class="max-h-40 rounded-lg border border-slate-700 object-contain">
                        </div>
                    @endif
                </div>
            @endif

            @if($appointment->notes)
                <div class="pt-2 border-t border-slate-700">
                    <p class="text-slate-400 text-sm mb-1">Notes</p>
                    <p class="text-slate-300 text-sm">{{ $appointment->notes }}</p>
                </div>
            @endif

            {{-- Event Brief --}}
            @if($appointment->isEventBooking())
            <div class="pt-3 border-t border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#0078D4] mb-3">Event Brief</p>
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
            </div>{{-- /p-6 body --}}
        </div>

        {{-- Timing conflict warning --}}
        @if($timingConflicts->isNotEmpty())
        @php
            $minutesOver = $appointment->actual_duration_minutes - $appointment->duration_minutes;
        @endphp
        <div class="bg-amber-900/20 border border-amber-700/50 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="shrink-0 w-5 h-5 text-amber-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-400">Timing conflict detected</p>
                    <p class="text-xs text-amber-400/70 mt-0.5">
                        Running {{ $minutesOver }}min over schedule overlaps
                        {{ $timingConflicts->count() === 1 ? 'the next booking' : $timingConflicts->count() . ' upcoming bookings' }}
                        for {{ $appointment->staff->name }}.
                    </p>
                    <div class="mt-3 space-y-2">
                        @foreach($timingConflicts as $conflict)
                        @php
                            $overlapMins   = $appointment->actual_duration_minutes
                                - (int) $conflict->scheduled_at->diffInMinutes($appointment->scheduled_at);
                            $conflictPhone = preg_replace('/[^0-9]/', '', $conflict->customer->phone ?? '');
                            if (strlen($conflictPhone) === 10 && str_starts_with($conflictPhone, '0')) {
                                $conflictPhone = '27' . substr($conflictPhone, 1);
                            }
                            $conflictMsg = "Hi {$conflict->customer->name}, just a heads-up — your appointment at {$conflict->scheduled_at->format('H:i')} may start a few minutes late. We'll be with you as soon as possible. Thank you for your patience!";
                            $conflictWA  = $conflictPhone ? 'https://wa.me/' . $conflictPhone . '?text=' . rawurlencode($conflictMsg) : null;
                        @endphp
                        <div class="flex items-center justify-between gap-3 bg-amber-950/40 rounded-lg px-3 py-2">
                            <div>
                                <a href="{{ route('appointments.show', $conflict) }}"
                                   class="text-sm font-medium text-white hover:text-amber-300 transition-colors">
                                    {{ $conflict->customer->name }}
                                </a>
                                <p class="text-xs text-amber-400/60 mt-0.5">
                                    Scheduled {{ $conflict->scheduled_at->format('H:i') }}
                                    — overlaps by ~{{ $overlapMins }}min
                                </p>
                            </div>
                            @if($conflictWA)
                            <a href="{{ $conflictWA }}" target="_blank" rel="noopener"
                               class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#25D366] hover:bg-[#1ebe5c] text-white text-xs font-bold rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Notify
                            </a>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions: status + WhatsApp + reminder + mark paid -->
        @if(!$appointment->sale)
            @php
                $phone = preg_replace('/[^0-9]/', '', $appointment->customer->phone ?? '');
                if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
                    $phone = '27' . substr($phone, 1);
                }
                $apptDate     = $appointment->scheduled_at->format('l, d F \Y \a\t H:i');
                $serviceNames = $appointment->services->pluck('name')->join(', ');
                $staffName    = $appointment->staff?->name ?? 'our team';
                $waAmount     = 'R' . number_format($grandTotal, 2);

                // Booking reminder message
                $bookingMsg = "Hi {$appointment->customer->name}! 👋 Just a friendly reminder that you have an appointment on {$apptDate} for {$serviceNames} with {$staffName}. Please let us know if you need to make any changes. See you then! 😊";
                $bookingReminderUrl = $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($bookingMsg) : null;

                // Payment reminder message
                $paymentMsg  = "Hi {$appointment->customer->name}, this is a reminder about your appointment on {$apptDate} for {$serviceNames}. An outstanding balance of {$waAmount} is due. Please contact us to arrange payment. Thank you!";
                $whatsappUrl = $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($paymentMsg) : null;
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

            {{-- Send reminder (booking WhatsApp + in-app/email) --}}
            @if(in_array($appointment->status, ['confirmed', 'pending', 'awaiting_payment']))
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-sm font-medium text-slate-300 mb-3">Send booking reminder to {{ $appointment->customer->name }}</p>
                    <div class="flex gap-2 flex-wrap">
                        @if($bookingReminderUrl)
                            <a href="{{ $bookingReminderUrl }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white text-xs font-bold rounded-lg transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp reminder
                            </a>
                        @endif
                        <form method="POST" action="{{ route('appointments.remind', $appointment) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-xs font-bold rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Email + in-app
                            </button>
                        </form>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        WhatsApp opens pre-filled: appointment date, time, service &amp; staff name.
                    </p>
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
                                class="text-xs px-3 py-1.5 rounded-lg {{ $appointment->status === $s ? 'bg-[#0078D4] text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }}">
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