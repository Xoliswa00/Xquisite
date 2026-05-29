@extends('layouts.booking')

@section('content')
<div class="space-y-8" x-data="slotPicker()">

    <nav class="flex items-center gap-2 text-sm text-slate-400">
        <a href="{{ route('book.index', $slug) }}" class="hover:text-indigo-600">Services</a>
        <span>&rsaquo;</span>
        <span class="text-slate-700 font-medium">{{ $service->name }}</span>
    </nav>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $service->name }}</h1>
            @if($service->description)
                <p class="text-slate-500 mt-1">{{ $service->description }}</p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-indigo-600">R{{ number_format($service->price, 2) }}</p>
            <p class="text-sm text-slate-400">{{ $service->duration_minutes }} min</p>
        </div>
    </div>

    {{-- Step 1: date --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <h2 class="text-base font-semibold text-slate-900">1. Choose a date</h2>
        <input type="date"
               x-model="selectedDate"
               :min="minDate"
               @change="loadSlots()"
               class="border-slate-300 rounded-xl text-slate-800 w-full sm:w-56">
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
            <input type="hidden" name="service_id" value="{{ $service->id }}">
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
            this.loading = true;
            this.selectedSlot = null;
            this.slots = [];
            try {
                const params = new URLSearchParams({
                    service_id: {{ $service->id }},
                    date: this.selectedDate,
                });
                const res = await fetch(`{{ route('book.slots', $slug) }}?${params}`);
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
