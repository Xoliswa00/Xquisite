@extends('layouts.booking')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Booking</h1>
            <p class="text-slate-500 text-sm mt-0.5">{{ $appointment->customer->name }}</p>
        </div>
        <a href="{{ route('book.my-bookings', $slug) }}"
           class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            ← Back to My Bookings
        </a>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-8">
        <form action="{{ route('book.update', [$slug, $appointment]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            {{-- Service (read-only display; updating service would require new slot availability checks) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Service</label>
                <input type="hidden" name="service_id" value="{{ $appointment->service_id }}">
                <p class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-lg px-3 py-2">
                   @php
    $serviceNames = $appointment->services->pluck('name')->join(', ');
    $duration = $appointment->services->sum(
        fn($service) => $service->pivot->duration_minutes ?? $service->duration_minutes
    );
@endphp

{{ $serviceNames }}
<span class="text-slate-400">
    ({{ $duration }} min)
</span>
                </p>
                <p class="text-xs text-slate-400 mt-1">To change service, please cancel and make a new booking.</p>
            </div>

            {{-- Date picker --}}
            <div>
                <label for="date" class="block text-sm font-medium text-slate-700 mb-2">Date</label>
                <input type="date"
                       id="date"
                       name="date"
                       min="{{ now()->toDateString() }}"
                       value="{{ old('date', $appointment->scheduled_at->toDateString()) }}"
                       required
                       class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg px-3 py-2
                              focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
            </div>

            {{-- Time slot picker --}}
            <div>
                <label for="time_slot" class="block text-sm font-medium text-slate-700 mb-2">Available Times</label>
                <select id="time_slot"
                        name="scheduled_at"
                        required
                        class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                    <option value="">Select a date first…</option>
                </select>
                <p id="slots-loading" class="text-xs text-slate-400 mt-1 hidden">Loading available times…</p>
                <p id="slots-empty"   class="text-xs text-red-500 mt-1 hidden">No slots available on this date.</p>
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">
                    Notes <span class="text-slate-400 font-normal">(optional)</span>
                </label>
                <textarea id="notes"
                          name="notes"
                          rows="3"
                          placeholder="Anything the team should know…"
                          class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg px-3 py-2
                                 focus:outline-none focus:ring-2 focus:ring-[#0078D4] resize-none">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('book.my-bookings', $slug) }}"
                   class="px-5 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-[#0078D4] hover:bg-[#0078D4] text-white px-6 py-2 rounded-xl text-sm font-semibold transition">
                    Save Changes
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    const dateInput    = document.getElementById('date');
    const slotSelect   = document.getElementById('time_slot');
    const loadingMsg   = document.getElementById('slots-loading');
    const emptyMsg     = document.getElementById('slots-empty');
    const serviceIds   = {!! $appointment->services->pluck('id')->toJson() !!};
    const currentSlot  = "{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d H:i')) }}";

    async function fetchSlots(date) {
        slotSelect.innerHTML = '';
        loadingMsg.classList.remove('hidden');
        emptyMsg.classList.add('hidden');

        try {
            const params = new URLSearchParams({ date });
            serviceIds.forEach(id => params.append('service_ids[]', id));
            const res    = await fetch(`{{ route('book.slots', $slug) }}?${params}`);
            const data   = await res.json();

            loadingMsg.classList.add('hidden');

            if (!data.slots?.length) {
                emptyMsg.classList.remove('hidden');
                slotSelect.innerHTML = '<option value="">No times available</option>';
                return;
            }

            slotSelect.innerHTML = data.slots.map(s =>
                `<option value="${s.value}" ${s.value === currentSlot ? 'selected' : ''}>${s.label}</option>`
            ).join('');

        } catch (e) {
            loadingMsg.classList.add('hidden');
            slotSelect.innerHTML = '<option value="">Error loading times</option>';
        }
    }

    dateInput.addEventListener('change', () => {
        if (dateInput.value) fetchSlots(dateInput.value);
    });

    // Load slots for the pre-selected date on page load
    if (dateInput.value) fetchSlots(dateInput.value);
</script>
@endsection