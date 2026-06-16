@extends('layouts.booking')

@section('content')
<div class="space-y-8" x-data="slotPicker()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-400">
        <a href="{{ route('book.index', $slug) }}" class="hover:text-indigo-600">Services</a>
        <span>&rsaquo;</span>
        <span class="text-slate-700 font-medium">Choose a time</span>
    </nav>

    {{-- Combo deal banner --}}
    @if($combo)
    <div class="rounded-2xl bg-gradient-to-r from-indigo-900 to-purple-900 border border-indigo-700/60 p-5 flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0 text-xl">✨</div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-white text-lg leading-tight">{{ $combo->name }}</p>
            <p class="text-indigo-300 text-sm mt-1">Bundle deal — all {{ $combo->services->count() }} services included</p>
        </div>
        <div class="shrink-0 text-right">
            <p class="text-xs text-white/40 line-through">R{{ number_format($combo->total_service_price, 2) }}</p>
            <p class="text-2xl font-black text-white">R{{ number_format($combo->combo_price, 2) }}</p>
            <p class="text-xs text-emerald-400 font-semibold">Save R{{ number_format($combo->savings, 2) }}</p>
        </div>
    </div>
    @endif

    {{-- Selected services summary --}}
    @php
        $comboServiceIds = $combo ? $combo->services->pluck('id')->all() : [];
        $comboServices   = $combo ? $services->filter(fn($s) => in_array($s->id, $comboServiceIds)) : collect();
        $extraServices   = $combo ? $services->filter(fn($s) => !in_array($s->id, $comboServiceIds)) : $services;
        $extrasCost      = $extraServices->sum('price');
        $grandTotal      = $combo ? $combo->combo_price + $extrasCost : $services->sum('price');
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
        <h1 class="text-xl font-bold text-slate-900">Your selected services</h1>
        <div class="divide-y divide-slate-100">
            @foreach($services as $service)
                @php $inCombo = $combo && in_array($service->id, $comboServiceIds); @endphp
                <div class="flex items-center justify-between py-2.5 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-700 font-medium">{{ $service->name }}</span>
                        @if($inCombo)
                            <span class="text-xs bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full">combo</span>
                        @endif
                    </div>
                    <span class="text-slate-500">
                        {{ $service->duration_minutes }} min
                        @if($inCombo)
                            &nbsp;·&nbsp;<span class="text-slate-400 line-through text-xs">R{{ number_format($service->price, 2) }}</span>
                        @else
                            &nbsp;·&nbsp;<span class="text-slate-700 font-semibold">R{{ number_format($service->price, 2) }}</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
        @if($combo)
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs text-indigo-600 font-semibold">
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
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-sm font-semibold">
            <span class="text-slate-700">Total</span>
            <span class="text-indigo-600">
                {{ $services->sum('duration_minutes') }} min &nbsp;·&nbsp;R{{ number_format($grandTotal, 2) }}
            </span>
        </div>
    </div>

    {{-- Step 1: date --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <h2 class="text-base font-semibold text-slate-900">1. Choose a date</h2>
        <input type="date"
               x-model="selectedDate"
               :min="minDate"
               @change="loadSlots()"
               @input="loadSlots()"
               class="border-slate-300 rounded-xl text-slate-800 w-full sm:w-56">
        <p class="text-xs text-slate-400 mt-1">Click the calendar icon or type your date to see available times.</p>
    </div>

    {{-- Step 2: time --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4" x-show="selectedDate" x-cloak>
        <h2 class="text-base font-semibold text-slate-900">2. Choose a time</h2>

        <p x-show="loading" class="text-sm text-slate-400 py-4">Checking availability&hellip;</p>

        <p x-show="!loading && slots.length === 0 && selectedDate"
           class="text-sm text-slate-400 py-4 bg-slate-50 rounded-xl px-4">
            No availability on this date. Please try another day.
        </p>

        <div class="grid grid-cols-4 gap-2" x-show="!loading && slots.length > 0">
            <template x-for="slot in slots" :key="slot.value">
                <button type="button"
                        @click="selectedSlot = slot.value"
                        :class="selectedSlot === slot.value
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-slate-700 border-slate-200 hover:border-indigo-400'"
                        class="py-2.5 text-sm font-medium border rounded-xl transition"
                        x-text="slot.label">
                </button>
            </template>
        </div>
    </div>

    {{-- Proceed --}}
    <div x-show="selectedSlot" x-cloak>
        <form method="GET" action="{{ route('book.confirm', $slug) }}">
            @foreach($services as $service)
                <input type="hidden" name="service_ids[]" value="{{ $service->id }}">
            @endforeach
            @if($combo)
                <input type="hidden" name="combo_id" value="{{ $combo->id }}">
            @endif
            <input type="hidden" name="scheduled_at" x-bind:value="selectedSlot">
            <button type="submit"
                    class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl transition text-lg">
                Continue &rarr;
            </button>
        </form>
    </div>

    <p class="text-xs text-slate-400 text-center">
        A staff member will be assigned to your booking by the team.
    </p>

</div>

@push('scripts')
<script>
function slotPicker() {
    return {
        selectedDate: '',
        selectedSlot: null,
        slots: [],
        loading: false,
        minDate: new Date().toISOString().split('T')[0],

        async loadSlots() {
            if (!this.selectedDate) return;
            this.loading      = true;
            this.selectedSlot = null;
            this.slots        = [];
            try {
                // Pass all service IDs so the backend can calculate combined duration
                const params = new URLSearchParams({ date: this.selectedDate });
                @foreach($services as $service)
                    params.append('service_ids[]', {{ $service->id }});
                @endforeach

                const res  = await fetch(`{{ route('book.slots', $slug) }}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                this.slots = data.slots ?? [];
            } catch (e) {
                this.slots = [];
            }
            this.loading = false;
        },
    };
}
</script>
@endpush
@endsection