@extends('layouts.booking')

@section('content')
<div class="space-y-8">

    <nav class="flex items-center gap-2 text-sm text-slate-400">
        <a href="{{ route('book.index', $slug) }}" class="hover:text-[#0078D4]">Services</a>
        <span>&rsaquo;</span>
        <a href="{{ route('book.service', $slug) }}?{{ http_build_query(['service_ids' => $services->pluck('id')->all()]) }}"
           class="hover:text-[#0078D4]">Change time</a>
        <span>&rsaquo;</span>
        <span class="text-slate-700 font-medium">Confirm</span>
    </nav>

    <h1 class="text-2xl font-bold text-slate-900">Confirm your booking</h1>

    {{-- Combo deal banner --}}
    @if($combo)
    <div class="rounded-2xl bg-gradient-to-r from-[#002B5B] to-[#001A3A] border border-[#002B5B]/60 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0 text-xl">✨</div>
        <div class="flex-1">
            <p class="font-black text-white">{{ $combo->name }}</p>
            <p class="text-[#B8D4F0] text-sm">Combo deal applied</p>
        </div>
        <div class="text-right shrink-0">
            <p class="text-xs text-white/40 line-through">R{{ number_format($combo->total_service_price, 2) }}</p>
            <p class="text-2xl font-black text-white">R{{ number_format($combo->combo_price, 2) }}</p>
            <p class="text-xs text-emerald-400 font-semibold">You save R{{ number_format($combo->savings, 2) }}</p>
        </div>
    </div>
    @endif

    @php
        $comboServiceIds = $combo ? $combo->services->pluck('id')->all() : [];
        $extraServices   = $combo ? $services->filter(fn($s) => !in_array($s->id, $comboServiceIds)) : collect();
        $extrasCost      = $extraServices->sum('price');
        $grandTotal      = $combo ? $combo->combo_price + $extrasCost : $services->sum('price');
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">

            {{-- Services list --}}
            <div class="col-span-2">
                <p class="text-slate-400 text-xs uppercase font-semibold mb-2">Services</p>
                <div class="divide-y divide-slate-100">
                    @foreach($services as $service)
                        @php $inCombo = $combo && in_array($service->id, $comboServiceIds); @endphp
                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-900">{{ $service->name }}</span>
                                @if($inCombo)
                                    <span class="text-xs bg-[#E8F2FA] text-[#002B5B] font-bold px-2 py-0.5 rounded-full">combo</span>
                                @endif
                            </div>
                            <span class="text-slate-500">
                                {{ $service->duration_minutes }} min
                                @if($inCombo)
                                    &middot; <span class="line-through text-xs">R{{ number_format($service->price, 2) }}</span>
                                @else
                                    &middot; R{{ number_format($service->price, 2) }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
                @if($combo)
                    <div class="flex items-center justify-between pt-2 text-xs text-[#0078D4] font-semibold">
                        <span>{{ $combo->name }}</span>
                        <span>R{{ number_format($combo->combo_price, 2) }}</span>
                    </div>
                    @if($extraServices->isNotEmpty())
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Add-on services</span>
                            <span>R{{ number_format($extrasCost, 2) }}</span>
                        </div>
                    @endif
                @endif
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-sm font-semibold">
                    <span class="text-slate-700">Total</span>
                    <span class="text-[#0078D4]">
                        {{ $services->sum('duration_minutes') }} min &middot; R{{ number_format($grandTotal, 2) }}
                    </span>
                </div>
            </div>

            {{-- Staff --}}
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">Staff</p>
                <p class="text-slate-400 mt-1 italic text-sm">Assigned on confirmation</p>
            </div>

            {{-- Date & time --}}
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">Date &amp; Time</p>
                <p class="font-semibold text-slate-900 mt-1">{{ $slot->format('l, d F Y') }}</p>
                <p class="text-slate-500">
                    {{ $slot->format('H:i') }}
                    &ndash;
                    {{ $slot->copy()->addMinutes($services->sum('duration_minutes'))->format('H:i') }}
                </p>
            </div>

        </div>
    </div>

    @if($customer)
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-700">
            Booking as <strong>{{ $customer->name }}</strong> ({{ $customer->email }})
        </div>

        <form method="POST" action="{{ route('book.store', $slug) }}">
            @csrf
            @if($combo)
                <input type="hidden" name="combo_id" value="{{ $combo->id }}">
            @endif
            <div class="space-y-4">

                {{-- Promo code — only available when no combo is active --}}
                @if($combo)
                    <div class="flex items-center gap-3 bg-[#F0F7FF] border border-[#DCEEFA] rounded-xl px-4 py-3 text-sm text-[#002B5B]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Promo codes cannot be combined with combo deals.
                    </div>
                @else
                    <div x-data="promoChecker('{{ route('book.promo.check', $slug) }}', {{ $grandTotal }})"
                         class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                        <p class="text-sm font-semibold text-slate-700">Have a promo code?</p>

                        @error('promo_code')
                            <p class="text-red-600 text-xs">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="code"
                                   @input="reset()"
                                   @keydown.enter.prevent="check()"
                                   placeholder="Enter code"
                                   autocomplete="off"
                                   spellcheck="false"
                                   style="text-transform:uppercase"
                                   class="flex-1 border-slate-300 rounded-xl text-sm tracking-wider focus:ring-[#0078D4] focus:border-[#0078D4] transition"
                                   :class="promoStatus === 'invalid' ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : (promoStatus === 'valid' ? 'border-emerald-400 focus:border-emerald-400 focus:ring-emerald-400' : '')">
                            <button type="button"
                                    @click="check()"
                                    :disabled="!code.trim() || promoStatus === 'checking'"
                                    class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-sm font-semibold rounded-xl transition disabled:opacity-40 shrink-0"
                                    x-text="promoStatus === 'checking' ? '...' : 'Apply'">
                            </button>
                        </div>

                        <p x-show="promoStatus === 'invalid'" x-cloak x-text="promoMessage" class="text-red-600 text-xs"></p>

                        <div x-show="promoStatus === 'valid'" x-cloak
                             class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
                            <div>
                                <p class="text-emerald-700 font-semibold text-sm">
                                    Code applied: <span x-text="promoLabel" class="uppercase"></span>
                                </p>
                                <p class="text-emerald-600 text-xs">You save R<span x-text="promoDiscount"></span></p>
                            </div>
                            <p class="text-emerald-800 font-black text-xl">R<span x-text="promoNewTotal"></span></p>
                        </div>

                        {{-- Hidden input — only set when promo is validated --}}
                        <input type="hidden" name="promo_code" :value="promoStatus === 'valid' ? code.trim().toUpperCase() : ''">
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
                    <textarea name="notes" rows="3" placeholder="Any special requests or information&hellip;"
                              class="w-full border-slate-300 rounded-xl text-sm"></textarea>
                </div>
                <button type="submit"
                        class="w-full py-3 bg-[#0078D4] hover:bg-[#0078D4] text-white font-semibold rounded-xl transition text-lg">
                    Confirm Booking
                </button>
            </div>
        </form>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
            You need an account to complete your booking. Your slot is held while you sign in.
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <a href="{{ route('book.login', $slug) }}"
               class="block text-center py-3 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl transition">
                Sign in to existing account
            </a>
            <a href="{{ route('book.register', $slug) }}"
               class="block text-center py-3 bg-[#0078D4] hover:bg-[#0078D4] text-white font-semibold rounded-xl transition">
                Create a new account
            </a>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function promoChecker(checkUrl, grandTotal) {
    return {
        code: '',
        promoStatus: 'idle',   // idle | checking | valid | invalid
        promoMessage: '',
        promoLabel: '',
        promoDiscount: '0.00',
        promoNewTotal: Number(grandTotal).toFixed(2),

        reset() {
            this.promoStatus  = 'idle';
            this.promoMessage = '';
        },

        async check() {
            const trimmed = this.code.trim();
            if (!trimmed) return;
            this.promoStatus = 'checking';
            try {
                const res = await fetch(checkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ code: trimmed }),
                });
                const data = await res.json();
                if (data.valid) {
                    this.promoStatus   = 'valid';
                    this.promoLabel    = data.label;
                    this.promoDiscount = data.discount;
                    this.promoNewTotal = data.new_total;
                } else {
                    this.promoStatus  = 'invalid';
                    this.promoMessage = data.message;
                }
            } catch {
                this.promoStatus  = 'invalid';
                this.promoMessage = 'Could not check code. Please try again.';
            }
        },
    };
}
</script>
@endpush