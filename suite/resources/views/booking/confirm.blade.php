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
        $extrasCost      = $extraServices->sum(fn($s) => $s->calculatePrice($quantities[$s->id] ?? 1));
        $grandTotal      = $combo ? $combo->combo_price + $extrasCost : $services->sum(fn($s) => $s->calculatePrice($quantities[$s->id] ?? 1));
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">

            {{-- Services list --}}
            <div class="col-span-2">
                <p class="text-slate-400 text-xs uppercase font-semibold mb-2">Services</p>
                <div class="divide-y divide-slate-100">
                    @foreach($services as $service)
                        @php
                            $inCombo = $combo && in_array($service->id, $comboServiceIds);
                            $qty     = $quantities[$service->id] ?? 1;
                        @endphp
                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-900">{{ $service->name }}</span>
                                @if($inCombo)
                                    <span class="text-xs bg-[#E8F2FA] text-[#002B5B] font-bold px-2 py-0.5 rounded-full">combo</span>
                                @endif
                                @if($service->pricing_type !== 'flat')
                                    <span class="text-xs text-slate-400">&times; {{ $qty }} {{ $service->unit_label ?? ($service->pricing_type === 'per_head' ? 'guests' : 'units') }}</span>
                                @endif
                            </div>
                            <span class="text-slate-500">
                                {{ $service->duration_minutes }} min
                                @if($inCombo)
                                    &middot; <span class="line-through text-xs">R{{ number_format($service->calculatePrice($qty), 2) }}</span>
                                @else
                                    &middot; R{{ number_format($service->calculatePrice($qty), 2) }}
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
                @if($isMultiDay)
                    <p class="text-slate-400 text-xs uppercase font-semibold">Booking Period</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ $slot->format('l, d F Y') }}</p>
                    <p class="text-slate-500">
                        to {{ $slot->copy()->addDays($totalDays - 1)->format('l, d F Y') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $totalDays }} days · schedule confirmed by team</p>
                @else
                    <p class="text-slate-400 text-xs uppercase font-semibold">Date &amp; Time</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ $slot->format('l, d F Y') }}</p>
                    <p class="text-slate-500">
                        {{ $slot->format('H:i') }}
                        &ndash;
                        {{ $slot->copy()->addMinutes($services->sum('duration_minutes'))->format('H:i') }}
                    </p>
                @endif
            </div>

        </div>
    </div>

    {{-- ── PAYMENT DETAILS ── --}}
    @if($tenant->bank_name && $tenant->bank_account_number)
    <div class="bg-white rounded-2xl border border-slate-200 p-6" x-data="{ copied: false }">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Payment Details</p>
        <p class="text-sm text-slate-500 mb-4">Pay via EFT using the details below to secure your booking.</p>
        <div class="space-y-2.5">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-slate-400">Bank</span>
                <span class="text-xs font-semibold text-slate-700">{{ $tenant->bank_name }}</span>
            </div>
            @if($tenant->bank_account_holder)
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-slate-400">Account Holder</span>
                <span class="text-xs font-semibold text-slate-700">{{ $tenant->bank_account_holder }}</span>
            </div>
            @endif
            @if($tenant->bank_account_type)
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-slate-400">Account Type</span>
                <span class="text-xs font-semibold text-slate-700">{{ ucfirst($tenant->bank_account_type) }}</span>
            </div>
            @endif
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-slate-400">Account No.</span>
                <span class="text-xs font-semibold text-slate-700 font-mono">{{ $tenant->bank_account_number }}</span>
            </div>
            @if($tenant->bank_branch_code)
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-slate-400">Branch Code</span>
                <span class="text-xs font-semibold text-slate-700 font-mono">{{ $tenant->bank_branch_code }}</span>
            </div>
            @endif
        </div>
        <button type="button"
                @click="navigator.clipboard.writeText('{{ $tenant->bank_account_number }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                class="mt-3 w-full flex items-center justify-center gap-1.5 py-2 rounded-xl border text-xs font-semibold transition-all"
                :class="copied ? 'border-emerald-300 bg-emerald-50 text-emerald-600' : 'border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
            <span x-text="copied ? 'Copied!' : 'Copy account number'"></span>
        </button>
    </div>
    @endif

    @if($customer)
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-700">
            Booking as <strong>{{ $customer->name }}</strong> ({{ $customer->email }})
        </div>

        <form method="POST" action="{{ route('book.store', $slug) }}"
              x-data="{ submitting: false }"
              @submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
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
                        :disabled="submitting"
                        class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition text-lg flex items-center justify-center gap-2">
                    <svg x-show="submitting" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Confirming…' : 'Confirm Booking'"></span>
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
               class="block text-center py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl transition">
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