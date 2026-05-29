@extends('layouts.booking')

@section('content')
<div class="space-y-8" x-data="slotPicker()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-400">
        <a href="{{ route('book.index', $slug) }}" class="hover:text-indigo-600">Services</a>
        <span>â€º</span>
        <span class="text-slate-700 font-medium">{{ $service->name }}</span>
    </nav>

    {{-- Service summary --}}
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

    @if($staff->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-400">
            No staff members are currently assigned to this service.
        </div>
    @else

    {{-- Step 1: Pick staff --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <h2 class="text-base font-semibold text-slate-900">1. Choose a staff member</h2>
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($staff as $member)
                <button type="button"
                        @click="selectStaff({{ $member->id }}, '{{ addslashes($member->name) }}')"
                        :class="selectedStaffId === {{ $member->id }} ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-300' : 'border-slate-200 hover:border-slate-300'"
                        class="text-left p-4 border rounded-xl transition">
                    <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                    @if($member->role)
                        <p class="text-sm text-slate-400">{{ $member->role }}</p>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Step 2: Pick a date --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4" x-show="selectedStaffId">
        <h2 class="text-base font-semibold text-slate-900">2. Choose a date</h2>
        <input type="date"
               x-model="selectedDate"
               :min="minDate"
               @change="loadSlots()"
               class="border-slate-300 rounded-xl text-slate-800 w-full sm:w-56">
    </div>

    {{-- Step 3: Pick a time slot --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4" x-show="selectedDate && !loading">
        <h2 class="text-base font-semibold text-slate-900">3. Choose a time</h2>

        <div x-show="loading" class="text-sm text-slate-400 py-4">Loading available timesâ€¦</div>

        <div x-show="!loading && slots.length === 0 && selectedDate" class="text-sm text-slate-400 py-4">
            No available times on this date. Please choose another day.
        </div>

        <div class="grid grid-cols-4 gap-2" x-show="!loading && slots.length > 0">
            <template x-for="slot in slots" :key="slot.value">
                <button type="button"
                        @click="selectSlot(slot)"
                        :class="selectedSlot === slot.value ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200 hover:border-indigo-400'"
                        class="py-2 text-sm font-medium border rounded-lg transition"
                        x-text="slot.label">
                </button>
            </template>
        </div>
    </div>

    {{-- Step 4: Proceed button --}}
    <div x-show="selectedSlot">
        <form method="GET" action="{{ route('book.confirm', $slug) }}">
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="hidden" name="staff_id" x-bind:value="selectedStaffId">
            <input type="hidden" name="scheduled_at" x-bind:value="selectedSlot">
            <button type="submit"
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition text-lg">
                Continue to Confirm â†’
            </button>
        </form>
    </div>

    @endif
</div>

@push('scripts')
<script>
function slotPicker() {
    return {
        selectedStaffId: null,
        selectedStaffName: null,
        selectedDate: '',
        selectedSlot: null,
        slots: [],
        loading: false,
        minDate: new Date().toISOString().split('T')[0],

        selectStaff(id, name) {
            this.selectedStaffId = id;
            this.selectedStaffName = name;
            this.selectedDate = '';
            this.selectedSlot = null;
            this.slots = [];
        },

        selectSlot(slot) {
            this.selectedSlot = slot.value;
        },

        async loadSlots() {
            if (!this.selectedStaffId || !this.selectedDate) return;
            this.loading = true;
            this.selectedSlot = null;
            this.slots = [];

            const params = new URLSearchParams({
                staff_id: this.selectedStaffId,
                service_id: {{ $service->id }},
                date: this.selectedDate,
            });

            const res = await fetch(`{{ route('book.slots', $slug) }}?${params}`);
            const data = await res.json();
            this.slots = data.slots ?? [];
            this.loading = false;
        },
    };
}
</script>
@endsection
@endsection

