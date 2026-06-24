@extends('layouts.booking')

@section('title', $tenant->name . ' – Book an Appointment')

@section('content')
@php
    $categorised   = $services->filter(fn($s) => $s->category)->groupBy('service_category_id');
    $uncategorised = $services->filter(fn($s) => !$s->category);
@endphp

<div x-data="servicePicker()">

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- HERO                                                                       --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="relative overflow-hidden rounded-3xl mb-10 shadow-2xl">
    {{-- Layered gradient background --}}
    <div class="absolute inset-0 bg-gradient-to-br from-[#001A3A] via-[#002B5B] to-slate-900"></div>
    {{-- Glowing orbs --}}
    <div class="pointer-events-none absolute -top-32 -right-32 w-96 h-96 rounded-full bg-[#0078D4] opacity-20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-[#0078D4] opacity-10 blur-3xl"></div>
    <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 rounded-full bg-[#D4AF37] opacity-8 blur-3xl"></div>

    <div class="relative px-7 py-10 sm:px-12 sm:py-14">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">

            {{-- Logo --}}
            @if(!empty($tenant->logo_url))
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}"
                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-white/20 shadow-2xl shrink-0">
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-[#0078D4] to-[#002B5B] border border-white/20 shadow-2xl flex items-center justify-center shrink-0">
                    <span class="text-4xl sm:text-5xl font-black text-white">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                </div>
            @endif

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-[#B8D4F0] text-xs font-bold uppercase tracking-[0.2em] mb-2">
                    {{ ucwords(str_replace('_', ' ', $tenant->industry ?? 'Beauty & Wellness')) }}
                </p>
                <h1 class="text-3xl sm:text-5xl font-black text-white leading-[1.05] tracking-tight">
                    {{ $tenant->name }}
                </h1>
                <p class="text-white/50 text-sm sm:text-base mt-3 max-w-md">
                    Book your perfect experience — combos, specials, and individual services all in one place.
                </p>

                {{-- Stats strip --}}
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-5">
                    <div class="flex items-center gap-2 text-white/60 text-sm">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span>Open for bookings</span>
                    </div>
                    @if($combos->isNotEmpty())
                        <div class="flex items-center gap-2 text-white/60 text-sm">
                            <svg class="w-4 h-4 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            <span>{{ $combos->count() }} combo {{ Str::plural('deal', $combos->count()) }}</span>
                        </div>
                    @endif
                    @if($promotions->isNotEmpty())
                        <div class="flex items-center gap-2 text-amber-300/80 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>{{ $promotions->count() }} {{ Str::plural('special', $promotions->count()) }} running</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 text-white/60 text-sm">
                        <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>{{ $services->count() }} {{ Str::plural('service', $services->count()) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom CTA strip --}}
        <div class="mt-8 pt-6 border-t border-white/10 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-white/40 text-xs">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                No booking fee
            </div>
            <div class="flex items-center gap-2 text-white/40 text-xs">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Instant confirmation
            </div>
            <div class="flex items-center gap-2 text-white/40 text-xs">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Reminder by email
            </div>
            @if($combos->isNotEmpty())
                <span class="ml-auto hidden sm:flex items-center gap-2 text-[#B8D4F0]/80 text-xs font-semibold">
                    <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    Scroll to see our combo deals below
                </span>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TWO-COLUMN LAYOUT                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="lg:grid lg:grid-cols-3 lg:gap-8 lg:items-start">

{{-- ─── LEFT COLUMN ────────────────────────────────────────────────────────── --}}
<div class="lg:col-span-2 space-y-12">

{{-- ════════════════════════════════ COMBOS ════════════════════════════════ --}}
@if($combos->isNotEmpty())
<section>
    <div class="flex items-center gap-3 mb-5">
        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-[#0078D4] shadow-md shadow-[#DCEEFA]">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-900 leading-none">Combo Deals</h2>
            <p class="text-xs text-slate-400 mt-0.5">Bundle services and save more</p>
        </div>
        <div class="ml-auto flex items-center gap-1.5 bg-[#F0F7FF] border border-[#DCEEFA] text-[#0078D4] text-xs font-bold px-3 py-1.5 rounded-full">
            <div class="w-1.5 h-1.5 rounded-full bg-[#0078D4] animate-pulse"></div>
            {{ $combos->count() }} available
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        @foreach($combos as $i => $combo)
        @php
            $ids     = $combo->services->pluck('id')->toArray();
            $cPrice  = (float) $combo->combo_price;
            $fullP   = (float) $combo->total_service_price;
            $savings = (float) $combo->savings;
            $savePct = $fullP > 0 ? round($savings / $fullP * 100) : 0;
            $mins    = $combo->services->sum('duration_minutes');
            $isFeat  = $i === 0;
        @endphp
        <div class="group relative rounded-2xl overflow-hidden shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5"
             :class="isComboSelected({{ json_encode($ids) }}, {{ $combo->id }})
                 ? 'ring-2 ring-[#0078D4] ring-offset-2 shadow-xl shadow-[#E8F2FA]'
                 : ''">

            {{-- Background --}}
            <div class="absolute inset-0 {{ $isFeat ? 'bg-gradient-to-br from-[#002B5B] via-[#001A3A] to-[#001A3A]' : 'bg-gradient-to-br from-slate-900 to-[#001A3A]' }}"></div>
            <div class="pointer-events-none absolute -top-12 -right-12 w-48 h-48 rounded-full {{ $isFeat ? 'bg-[#0078D4]' : 'bg-[#002B5B]' }} opacity-20 blur-2xl group-hover:opacity-30 transition-opacity"></div>

            {{-- Badges --}}
            <div class="absolute top-4 right-4 flex flex-col items-end gap-1.5">
                @if($savePct > 0)
                <span class="bg-emerald-400 text-emerald-950 text-xs font-black px-2.5 py-1 rounded-full shadow-lg">
                    SAVE {{ $savePct }}%
                </span>
                @endif
                @if($isFeat)
                <span class="bg-amber-400 text-amber-950 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide">
                    ⭐ Featured
                </span>
                @endif
            </div>

            <div class="relative p-6">
                {{-- Title --}}
                <div class="mb-4 pr-24">
                    <p class="text-xl font-black text-white leading-tight">{{ $combo->name }}</p>
                    @if($combo->description)
                        <p class="text-sm text-white/50 mt-1 line-clamp-2">{{ $combo->description }}</p>
                    @endif
                </div>

                {{-- Services included --}}
                <div class="flex flex-wrap gap-1.5 mb-5">
                    @foreach($combo->services as $svc)
                    <span class="inline-flex items-center gap-1.5 text-xs bg-white/10 text-white/80 px-3 py-1 rounded-full border border-white/10 font-medium">
                        {{ $svc->name }}
                        <span class="text-white/40">· {{ $svc->duration_minutes }}min</span>
                    </span>
                    @endforeach
                </div>

                {{-- Price + CTA --}}
                <div class="flex items-end justify-between mb-4">
                    <div>
                        @if($fullP > $cPrice)
                            <p class="text-xs text-white/35 line-through mb-0.5">R{{ number_format($fullP, 2) }}</p>
                        @endif
                        <p class="text-3xl font-black text-white">R{{ number_format($cPrice, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-white/40">{{ $mins }} min total</p>
                        @if($savings > 0)
                            <p class="text-sm font-bold text-emerald-400">Save R{{ number_format($savings, 2) }}</p>
                        @endif
                    </div>
                </div>

                <button type="button"
                        @click="selectCombo({{ json_encode($ids) }}, {{ $cPrice }}, '{{ addslashes($combo->name) }}', {{ $combo->id }})"
                        class="w-full py-3 rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2"
                        :class="isComboSelected({{ json_encode($ids) }}, {{ $combo->id }})
                            ? 'bg-emerald-400 text-emerald-950 shadow-lg shadow-emerald-400/30'
                            : 'bg-white text-[#001A3A] hover:bg-[#F0F7FF] shadow-md'">
                    <template x-if="!isComboSelected({{ json_encode($ids) }}, {{ $combo->id }})">
                        <span class="flex items-center gap-2">
                            Book this combo
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </template>
                    <template x-if="isComboSelected({{ json_encode($ids) }}, {{ $combo->id }})">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Added — see summary →
                        </span>
                    </template>
                </button>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ════════════════════════════════ SPECIALS ══════════════════════════════ --}}
@if($promotions->isNotEmpty())
<section>
    <div class="flex items-center gap-3 mb-5">
        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-amber-500 shadow-md shadow-amber-200">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-900 leading-none">Current Specials</h2>
            <p class="text-xs text-slate-400 mt-0.5">Enter code at confirmation to redeem — not valid on combo deals</p>
        </div>
        <span class="ml-auto text-xs text-amber-600 font-bold bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-full">
            {{ $promotions->count() }} on now
        </span>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        @foreach($promotions as $promo)
        <div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="pointer-events-none absolute -top-8 -right-8 w-32 h-32 rounded-full bg-amber-200 opacity-40"></div>
            <div class="relative">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-900 leading-snug">{{ $promo->name }}</p>
                        @if($promo->description)
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $promo->description }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 bg-amber-500 text-white rounded-2xl px-3.5 py-2 text-center shadow-md shadow-amber-200 min-w-[76px]">
                        <p class="text-xl font-black leading-none">
                            {{ $promo->discount_type === 'percentage' ? $promo->discount_value . '%' : 'R' . number_format($promo->discount_value, 0) }}
                        </p>
                        <p class="text-[10px] font-bold opacity-90 uppercase tracking-widest mt-0.5">OFF</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2.5 bg-white border border-amber-200 rounded-xl px-3.5 py-2 shadow-sm flex-1">
                        <span class="text-xs text-slate-400 font-medium">Code</span>
                        <code class="font-black text-amber-700 tracking-wider flex-1">{{ $promo->code }}</code>
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ $promo->code }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                class="shrink-0 text-xs font-bold px-2 py-0.5 rounded-md transition-all"
                                :class="copied ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700 hover:bg-amber-200'">
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    @if($promo->valid_until)
                        <span class="text-xs text-slate-400 flex items-center gap-1 whitespace-nowrap">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Ends {{ $promo->valid_until->format('d M') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ════════════════════════════════ SERVICES ══════════════════════════════ --}}
<section>
    <div class="flex items-center gap-3 mb-5">
        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-slate-800 shadow-md">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-900 leading-none">
                {{ ($combos->isNotEmpty() || $promotions->isNotEmpty()) ? 'Individual Services' : 'Our Services' }}
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Pick one or mix and match multiple</p>
        </div>
    </div>

    @if($services->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-16 text-center">
            <div class="text-4xl mb-3">🌿</div>
            <p class="text-slate-400 text-sm">No services available right now. Check back soon.</p>
        </div>
    @else

        {{-- Categorised --}}
        @foreach($categorised as $catId => $catServices)
        @php
            $cat      = $catServices->first()->category;
            $classes  = \App\Models\ServiceCategory::colorClasses()[$cat->color] ?? [];
            $dotClass = $classes['dot']  ?? 'bg-slate-400';
        @endphp
        <div class="mb-6" x-data="{ open: true }">
            {{-- Category header (tap to collapse) --}}
            <div class="flex items-center gap-3 mb-3 cursor-pointer select-none" @click="open = !open">
                <div class="w-1 h-7 rounded-full {{ $dotClass }} transition-opacity" :class="open ? 'opacity-100' : 'opacity-40'"></div>
                <span class="text-base font-bold text-slate-900">{{ $cat->icon }} {{ $cat->name }}</span>
                <span class="text-xs text-slate-400 font-medium bg-slate-100 px-2 py-0.5 rounded-full">
                    {{ $catServices->count() }}
                </span>
                <svg class="w-4 h-4 text-slate-400 ml-auto transition-transform duration-200" :class="open ? '' : '-rotate-90'"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div x-show="open" x-cloak class="grid sm:grid-cols-2 gap-3">
                @foreach($catServices as $service)
                @php
                    $desc      = $service->description ?? '';
                    $descLines = $desc ? array_values(array_filter(array_map('trim', explode("\n", $desc)))) : [];
                    $isList    = count($descLines) > 1 && collect($descLines)->every(fn($l) => preg_match('/^[-*•·]|\d+[.)]\s/', $l));
                    $listItems = $isList ? array_map(fn($l) => trim(preg_replace('/^[-*•·]\s*|\d+[.)]\s*/', '', $l)), $descLines) : [];
                    $isLong    = $desc && (strlen($desc) > 100 || count($descLines) > 2);
                    $needsMore = $isLong || count($listItems) > 2;
                @endphp
                <div role="button" tabindex="0"
                        @click="toggle({{ $service->id }})"
                        @keydown.enter.stop="toggle({{ $service->id }})"
                        @keydown.space.prevent.stop="toggle({{ $service->id }})"
                        class="group relative text-left w-full rounded-2xl border bg-white p-4 cursor-pointer transition-all duration-200 overflow-hidden select-none"
                        :class="selected.includes({{ $service->id }})
                            ? 'border-[#0078D4] bg-[#F0F7FF]/80 ring-2 ring-[#0078D4] ring-offset-1 shadow-lg shadow-[#E8F2FA]'
                            : 'border-slate-200 hover:border-slate-300 hover:shadow-md hover:bg-slate-50/50'">

                    {{-- Left accent stripe --}}
                    <div class="absolute top-0 left-0 w-1 h-full {{ $dotClass }} transition-opacity"
                         :class="selected.includes({{ $service->id }}) ? 'opacity-100' : 'opacity-40'"></div>

                    {{-- Check ring --}}
                    <div x-show="selected.includes({{ $service->id }})" x-cloak
                         class="absolute top-3.5 right-3.5 w-6 h-6 bg-[#0078D4] rounded-full flex items-center justify-center shadow-md ring-2 ring-white">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    {{-- Plus ring (unselected) --}}
                    <div x-show="!selected.includes({{ $service->id }})"
                         class="absolute top-3.5 right-3.5 w-6 h-6 bg-slate-100 group-hover:bg-[#E8F2FA] group-hover:border-[#B8D4F0] rounded-full flex items-center justify-center border border-slate-200 transition-all">
                        <svg class="w-3 h-3 text-slate-400 group-hover:text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>

                    <div class="pl-3 pr-10">
                        <p class="font-semibold text-sm text-slate-900 group-hover:text-[#002B5B] transition-colors leading-snug"
                           :class="selected.includes({{ $service->id }}) ? '!text-[#002B5B]' : ''">
                            {{ $service->name }}
                        </p>
                        @if($desc)
                        <div x-data="{ open: false }" class="mt-1">
                            {{-- Collapsed --}}
                            <div x-show="!open">
                                @if($isList)
                                    <ul class="text-xs text-slate-400 leading-relaxed space-y-0.5 list-disc list-inside">
                                        @foreach(array_slice($listItems, 0, 2) as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                        @if(count($listItems) > 2)
                                            <li class="list-none pl-4 text-slate-300">+{{ count($listItems) - 2 }} more…</li>
                                        @endif
                                    </ul>
                                @else
                                    <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">{{ $desc }}</p>
                                @endif
                            </div>
                            {{-- Expanded --}}
                            <div x-show="open" x-cloak>
                                @if($isList)
                                    <ul class="text-xs text-slate-400 leading-relaxed space-y-0.5 list-disc list-inside">
                                        @foreach($listItems as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-slate-400 leading-relaxed whitespace-pre-line">{{ $desc }}</p>
                                @endif
                            </div>
                            @if($needsMore)
                            <button type="button" @click.stop="open = !open"
                                    class="mt-1 flex items-center gap-1 text-xs text-[#0078D4] font-medium hover:underline focus:outline-none">
                                <span x-text="open ? 'Show less' : 'Read more'"></span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="open ? '-rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            @endif
                        </div>
                        @endif
                        <div class="flex items-center justify-between mt-3.5">
                            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $service->duration_minutes }} min
                            </div>
                            <p class="font-bold text-slate-900 text-sm"
                               :class="selected.includes({{ $service->id }}) ? '!text-[#002B5B]' : ''">
                                R{{ number_format($service->price, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Uncategorised --}}
        @if($uncategorised->isNotEmpty())
        <div class="mb-6" x-data="{ open: true }">
            @if($categorised->isNotEmpty())
            <div class="flex items-center gap-3 mb-3 cursor-pointer select-none" @click="open = !open">
                <div class="w-1 h-7 rounded-full bg-slate-400 transition-opacity" :class="open ? 'opacity-100' : 'opacity-40'"></div>
                <span class="text-base font-bold text-slate-900">Other Services</span>
                <span class="text-xs text-slate-400 font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{ $uncategorised->count() }}</span>
                <svg class="w-4 h-4 text-slate-400 ml-auto transition-transform duration-200" :class="open ? '' : '-rotate-90'"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            @endif
            <div x-show="open" x-cloak class="grid sm:grid-cols-2 gap-3">
                @foreach($uncategorised as $service)
                @php
                    $desc      = $service->description ?? '';
                    $descLines = $desc ? array_values(array_filter(array_map('trim', explode("\n", $desc)))) : [];
                    $isList    = count($descLines) > 1 && collect($descLines)->every(fn($l) => preg_match('/^[-*•·]|\d+[.)]\s/', $l));
                    $listItems = $isList ? array_map(fn($l) => trim(preg_replace('/^[-*•·]\s*|\d+[.)]\s*/', '', $l)), $descLines) : [];
                    $isLong    = $desc && (strlen($desc) > 100 || count($descLines) > 2);
                    $needsMore = $isLong || count($listItems) > 2;
                @endphp
                <div role="button" tabindex="0"
                        @click="toggle({{ $service->id }})"
                        @keydown.enter.stop="toggle({{ $service->id }})"
                        @keydown.space.prevent.stop="toggle({{ $service->id }})"
                        class="group relative text-left w-full rounded-2xl border bg-white p-4 cursor-pointer transition-all duration-200 overflow-hidden select-none"
                        :class="selected.includes({{ $service->id }})
                            ? 'border-[#0078D4] bg-[#F0F7FF]/80 ring-2 ring-[#0078D4] ring-offset-1 shadow-lg shadow-[#E8F2FA]'
                            : 'border-slate-200 hover:border-slate-300 hover:shadow-md hover:bg-slate-50/50'">

                    <div class="absolute top-0 left-0 w-1 h-full bg-slate-400 transition-opacity"
                         :class="selected.includes({{ $service->id }}) ? 'opacity-100 !bg-[#0078D4]' : 'opacity-30'"></div>

                    <div x-show="selected.includes({{ $service->id }})" x-cloak
                         class="absolute top-3.5 right-3.5 w-6 h-6 bg-[#0078D4] rounded-full flex items-center justify-center shadow-md ring-2 ring-white">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div x-show="!selected.includes({{ $service->id }})"
                         class="absolute top-3.5 right-3.5 w-6 h-6 bg-slate-100 group-hover:bg-[#E8F2FA] group-hover:border-[#B8D4F0] rounded-full flex items-center justify-center border border-slate-200 transition-all">
                        <svg class="w-3 h-3 text-slate-400 group-hover:text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>

                    <div class="pl-3 pr-10">
                        <p class="font-semibold text-sm text-slate-900 group-hover:text-[#002B5B] transition-colors leading-snug"
                           :class="selected.includes({{ $service->id }}) ? '!text-[#002B5B]' : ''">
                            {{ $service->name }}
                        </p>
                        @if($desc)
                        <div x-data="{ open: false }" class="mt-1">
                            <div x-show="!open">
                                @if($isList)
                                    <ul class="text-xs text-slate-400 leading-relaxed space-y-0.5 list-disc list-inside">
                                        @foreach(array_slice($listItems, 0, 2) as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                        @if(count($listItems) > 2)
                                            <li class="list-none pl-4 text-slate-300">+{{ count($listItems) - 2 }} more…</li>
                                        @endif
                                    </ul>
                                @else
                                    <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">{{ $desc }}</p>
                                @endif
                            </div>
                            <div x-show="open" x-cloak>
                                @if($isList)
                                    <ul class="text-xs text-slate-400 leading-relaxed space-y-0.5 list-disc list-inside">
                                        @foreach($listItems as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-slate-400 leading-relaxed whitespace-pre-line">{{ $desc }}</p>
                                @endif
                            </div>
                            @if($needsMore)
                            <button type="button" @click.stop="open = !open"
                                    class="mt-1 flex items-center gap-1 text-xs text-[#0078D4] font-medium hover:underline focus:outline-none">
                                <span x-text="open ? 'Show less' : 'Read more'"></span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="open ? '-rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            @endif
                        </div>
                        @endif
                        <div class="flex items-center justify-between mt-3.5">
                            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $service->duration_minutes }} min
                            </div>
                            <p class="font-bold text-slate-900 text-sm"
                               :class="selected.includes({{ $service->id }}) ? '!text-[#002B5B]' : ''">
                                R{{ number_format($service->price, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    @endif
</section>

</div>{{-- end left column --}}

{{-- ─── RIGHT SIDEBAR ───────────────────────────────────────────────────────── --}}
<div class="hidden lg:block">
    <div class="sticky top-24 space-y-4">

        {{-- ── ORDER SUMMARY CARD ── --}}
        <div class="rounded-2xl overflow-hidden shadow-lg border border-slate-200">

            {{-- Header --}}
            <div class="bg-gradient-to-br from-slate-900 to-[#001A3A] px-5 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-[#B8D4F0] uppercase tracking-widest">Your Booking</p>
                        <p class="text-white font-bold text-sm mt-0.5 leading-tight"
                           x-text="selected.length === 0 ? 'Nothing selected yet' : (selectedCombo ? selectedCombo.name : selected.length + ' service' + (selected.length > 1 ? 's' : '') + ' selected')"></p>
                    </div>
                </div>
            </div>

            {{-- Empty state --}}
            <div x-show="selected.length === 0" class="bg-white px-5 py-10 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Ready when you are</p>
                <p class="text-xs text-slate-400">Select a service, combo, or multiple services on the left to build your booking.</p>
            </div>

            {{-- Selected state --}}
            <div x-show="selected.length > 0" x-cloak class="bg-white">

                {{-- Combo badge --}}
                <template x-if="selectedCombo">
                    <div class="mx-4 mt-4 flex items-center gap-2 p-3 bg-[#F0F7FF] border border-[#DCEEFA] rounded-xl">
                        <svg class="w-4 h-4 text-[#0078D4] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        <span class="text-xs text-[#002B5B] font-bold">Combo deal applied!</span>
                    </div>
                </template>

                {{-- Line items --}}
                <div class="px-4 py-3 space-y-2.5 max-h-60 overflow-y-auto">
                    <template x-for="id in selected" :key="id">
                        <div class="flex items-start gap-3 p-2.5 rounded-xl border"
                             :class="selectedCombo && selectedCombo.serviceIds.includes(id)
                                 ? 'bg-[#F0F7FF]/60 border-[#E8F2FA]'
                                 : 'bg-slate-50 border-slate-100'">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 leading-snug" x-text="services.find(s => s.id === id)?.name"></p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <p class="text-xs text-slate-400" x-text="(services.find(s => s.id === id)?.duration_minutes || 0) + ' min'"></p>
                                    <template x-if="selectedCombo && selectedCombo.serviceIds.includes(id)">
                                        <span class="text-xs text-[#0078D4] font-bold bg-[#E8F2FA] px-1.5 py-0.5 rounded-full">combo</span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 mt-0.5">
                                <p class="text-sm font-bold"
                                   :class="selectedCombo && selectedCombo.serviceIds.includes(id) ? 'text-slate-400 line-through text-xs' : 'text-slate-700'"
                                   x-text="'R' + (services.find(s => s.id === id)?.price || 0).toLocaleString('en-ZA', {minimumFractionDigits:2})"></p>
                                <button type="button" @click="toggle(id)"
                                        x-show="!(selectedCombo && selectedCombo.serviceIds.includes(id))"
                                        class="w-5 h-5 rounded-full bg-slate-200 hover:bg-red-100 text-slate-400 hover:text-red-500 flex items-center justify-center transition-colors shrink-0">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Totals --}}
                <div class="px-4 pb-4 space-y-2">
                    <div class="flex justify-between items-center text-xs text-slate-500 px-1">
                        <span>Est. duration</span>
                        <span class="font-semibold text-slate-700" x-text="totalDuration + ' min'"></span>
                    </div>
                    {{-- Combo price row --}}
                    <template x-if="selectedCombo">
                        <div class="flex justify-between text-xs text-[#0078D4] font-semibold px-1">
                            <span x-text="selectedCombo.name + ' deal'"></span>
                            <span x-text="'R' + selectedCombo.price.toLocaleString('en-ZA', {minimumFractionDigits:2})"></span>
                        </div>
                    </template>
                    {{-- Extras row (only when extras are added on top of combo) --}}
                    <template x-if="selectedCombo && selected.some(id => !selectedCombo.serviceIds.includes(id))">
                        <div class="flex justify-between text-xs text-slate-500 px-1">
                            <span>Add-on services</span>
                            <span x-text="'R' + selected.filter(id => !selectedCombo.serviceIds.includes(id)).reduce((s, id) => { const sv = services.find(x => x.id === id); return s + (sv ? sv.price : 0); }, 0).toLocaleString('en-ZA', {minimumFractionDigits:2})"></span>
                        </div>
                    </template>
                    {{-- Full price strikethrough (only when saving something) --}}
                    <template x-if="comboDeal">
                        <div class="flex justify-between text-xs text-slate-400 px-1">
                            <span>Full price</span>
                            <span class="line-through" x-text="'R' + fullPrice"></span>
                        </div>
                    </template>
                    <div class="bg-slate-900 rounded-xl px-4 py-3 flex items-center justify-between">
                        <span class="text-sm font-bold text-white">Total</span>
                        <span class="text-xl font-black text-white" x-text="'R' + totalPrice"></span>
                    </div>
                    <template x-if="comboDeal">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2 flex justify-between items-center">
                            <div class="flex items-center gap-1.5 text-emerald-700">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                                <span class="text-xs font-bold">You're saving</span>
                            </div>
                            <span class="text-sm font-black text-emerald-700" x-text="'R' + comboDeal.savings"></span>
                        </div>
                    </template>
                </div>

                {{-- CTA --}}
                <div class="px-4 pb-4">
                    <form method="GET" :action="'{{ route('book.service', $slug) }}'">
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="service_ids[]" :value="id">
                        </template>
                        <input type="hidden" name="combo_id" :value="selectedComboId || ''">
                        <button type="submit"
                                class="w-full bg-[#0078D4] hover:bg-[#0078D4] active:scale-[0.98] text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-[#DCEEFA] hover:shadow-xl hover:shadow-[#B8D4F0] flex items-center justify-center gap-2 text-sm">
                            Choose Date &amp; Time
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </form>
                    <button type="button" @click="clearSelection()"
                            class="w-full mt-2 text-xs text-slate-400 hover:text-slate-600 py-2 transition-colors flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear selection
                    </button>
                </div>
            </div>
        </div>

        {{-- ── TRUST SIGNALS ── --}}
        <div class="bg-white rounded-2xl border border-slate-100 px-5 py-4 space-y-3">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Why book online?</p>
            <div class="space-y-2.5">
                <div class="flex items-center gap-3 text-sm text-slate-600">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4H5z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-xs text-slate-700">Combo deals exclusive online</p>
                        <p class="text-xs text-slate-400">Bundles only available when booking here</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-600">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-xs text-slate-700">Instant confirmation</p>
                        <p class="text-xs text-slate-400">No waiting for a callback</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-600">
                    <div class="w-8 h-8 rounded-lg bg-[#0078D4]/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-xs text-slate-700">Reminder before your visit</p>
                        <p class="text-xs text-slate-400">We'll email you so you never forget</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LOCATION ── --}}
        @if($tenant->address)
        <div class="bg-white rounded-2xl border border-slate-100 px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Find Us</p>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#0078D4]/10 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-700">{{ $tenant->name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $tenant->address }}</p>
                    <a href="https://maps.google.com?q={{ urlencode($tenant->address) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1 mt-2 text-xs text-[#0078D4] hover:underline font-medium">
                        Open in Maps
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- ── BANKING DETAILS ── --}}
        @if($tenant->bank_name && $tenant->bank_account_number)
        <div class="bg-white rounded-2xl border border-slate-100 px-5 py-4"
             x-data="{ copied: false }">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Payment Details</p>
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

    </div>
</div>{{-- end sidebar --}}

</div>{{-- end two-column --}}

{{-- ── MOBILE: Location + Banking (below services, hidden on desktop) ────── --}}
@if($tenant->address || ($tenant->bank_name && $tenant->bank_account_number))
<div class="lg:hidden mt-10 grid sm:grid-cols-2 gap-4">

    @if($tenant->address)
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Find Us</p>
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#0078D4]/10 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-700">{{ $tenant->name }}</p>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $tenant->address }}</p>
                <a href="https://maps.google.com?q={{ urlencode($tenant->address) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 mt-2 text-xs text-[#0078D4] hover:underline font-medium">
                    Open in Maps
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
            </div>
        </div>
    </div>
    @endif

    @if($tenant->bank_name && $tenant->bank_account_number)
    <div class="bg-white rounded-2xl border border-slate-100 p-5"
         x-data="{ copiedM: false }">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Payment Details</p>
        <div class="space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-slate-400">Bank</span>
                <span class="text-xs font-semibold text-slate-700">{{ $tenant->bank_name }}</span>
            </div>
            @if($tenant->bank_account_holder)
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-slate-400">Holder</span>
                <span class="text-xs font-semibold text-slate-700">{{ $tenant->bank_account_holder }}</span>
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
                @click="navigator.clipboard.writeText('{{ $tenant->bank_account_number }}').then(() => { copiedM = true; setTimeout(() => copiedM = false, 2000) })"
                class="mt-3 w-full flex items-center justify-center gap-1.5 py-2 rounded-xl border text-xs font-semibold transition-all"
                :class="copiedM ? 'border-emerald-300 bg-emerald-50 text-emerald-600' : 'border-slate-200 text-slate-500 hover:border-slate-300'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
            <span x-text="copiedM ? 'Copied!' : 'Copy account number'"></span>
        </button>
    </div>
    @endif

</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MOBILE BOTTOM BAR                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="selected.length > 0"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-full"
     class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/98 backdrop-blur-xl border-t border-slate-200 shadow-2xl safe-area-inset-bottom">

    {{-- Combo deal indicator strip --}}
    <template x-if="comboDeal">
        <div class="bg-emerald-500 text-white text-xs font-bold text-center py-1.5 flex items-center justify-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Combo deal applied — you're saving
            <span x-text="'R' + comboDeal.savings"></span>
        </div>
    </template>

    <div class="flex items-center gap-3 px-4 py-3 max-w-6xl mx-auto">
        <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-900 text-sm leading-tight truncate"
               x-text="selectedCombo ? selectedCombo.name : selected.length + ' service' + (selected.length > 1 ? 's' : '') + ' selected'"></p>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xs text-slate-500" x-text="totalDuration + ' min'"></span>
                <span class="text-slate-300">·</span>
                <span class="text-sm font-black text-slate-900" x-text="'R' + totalPrice"></span>
            </div>
        </div>

        <button type="button" @click="clearSelection()"
                class="p-2.5 rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <form method="GET" :action="'{{ route('book.service', $slug) }}'">
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="service_ids[]" :value="id">
            </template>
            <input type="hidden" name="combo_id" :value="selectedComboId || ''">
            <button type="submit"
                    class="shrink-0 bg-[#0078D4] hover:bg-[#0078D4] active:scale-95 text-white font-bold px-5 py-3 rounded-xl transition-all shadow-lg shadow-[#B8D4F0] flex items-center gap-2 text-sm whitespace-nowrap">
                Book Now
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
function servicePicker() {
    return {
        selected: [],
        selectedCombo: null,
        selectedComboId: null,
        comboDeal: null,
        totalDuration: 0,
        totalPrice: '0.00',
        fullPrice: '0.00',
        services: @json($servicesJson),
        combos: @json($combosJson),

        toggle(id) {
            // Combo services are locked — they can only be removed via "Clear selection"
            if (this.selectedComboId && this.selectedCombo && this.selectedCombo.serviceIds.includes(id)) {
                return;
            }
            const idx = this.selected.indexOf(id);
            if (idx === -1) { this.selected.push(id); } else { this.selected.splice(idx, 1); }
            this.recalculate();
        },

        selectCombo(serviceIds, comboPrice, comboName, comboId) {
            if (this.isComboSelected(serviceIds, comboId)) { this.clearSelection(); return; }
            // Preserve any extras already in the basket; swap the combo services in
            const extras = this.selected.filter(id => !serviceIds.includes(id)
                && !(this.selectedCombo && this.selectedCombo.serviceIds.includes(id)));
            this.selected        = [...serviceIds, ...extras];
            this.selectedCombo   = { name: comboName, price: comboPrice, serviceIds: [...serviceIds] };
            this.selectedComboId = comboId;
            this.recalculate();
        },

        isComboSelected(serviceIds, comboId) {
            return this.selectedComboId === comboId
                && serviceIds.every(id => this.selected.includes(id));
        },

        clearSelection() {
            this.selected        = [];
            this.selectedCombo   = null;
            this.selectedComboId = null;
            this.comboDeal       = null;
            this.recalculate();
        },

        recalculate() {
            // Auto-detect a combo from the current selection when none is manually active
            if (!this.selectedComboId && this.selected.length >= 2) {
                const match = this.combos
                    .filter(c => c.serviceIds.length >= 2 && c.serviceIds.every(id => this.selected.includes(id)))
                    .sort((a, b) => b.savings - a.savings)[0];
                if (match) {
                    this.selectedCombo   = { name: match.name, price: match.price, serviceIds: [...match.serviceIds] };
                    this.selectedComboId = match.id;
                }
            }

            let mins = 0, full = 0, extrasCost = 0;
            const comboIds = this.selectedCombo ? this.selectedCombo.serviceIds : [];

            this.selected.forEach(id => {
                const s = this.services.find(s => s.id === id);
                if (s) {
                    mins  += s.duration_minutes;
                    full  += s.price;
                    if (!comboIds.includes(id)) extrasCost += s.price;
                }
            });

            this.totalDuration = mins;
            this.fullPrice     = full.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Total = combo deal price + any extras at full individual price
            const price = this.selectedCombo ? this.selectedCombo.price + extrasCost : full;
            this.totalPrice = price.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const savings = full - price;
            this.comboDeal = (this.selectedCombo && savings > 0)
                ? { savings: savings.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
                : null;
        },
    };
}
</script>
@endpush
@endsection
