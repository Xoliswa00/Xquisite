@extends('layouts.booking')

@section('title', $tenant->name . ' – Book')

@section('content')
<div class="space-y-8" x-data="servicePicker()">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Book an Appointment</h1>
        <p class="text-slate-500 mt-1">Select one or more services to get started.</p>
    </div>

    @if($services->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
            No services available yet. Check back soon.
        </div>
    @else
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($services as $service)
                <button type="button"
                        @click="toggle({{ $service->id }})"
                        :class="selected.includes({{ $service->id }})
                            ? 'border-indigo-500 bg-indigo-50 shadow-sm'
                            : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-sm'"
                        class="group rounded-2xl border p-6 transition text-left w-full relative">

                    {{-- Checkmark --}}
                    <span x-show="selected.includes({{ $service->id }})"
                          class="absolute top-4 right-4 w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>

                    <div class="pr-6">
                        <h2 class="text-lg font-semibold text-slate-900 group-hover:text-indigo-600 transition"
                            :class="selected.includes({{ $service->id }}) ? 'text-indigo-700' : ''">
                            {{ $service->name }}
                        </h2>
                        @if($service->description)
                            <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 mt-4 text-sm text-slate-500">
                        <span>{{ $service->duration_minutes }} min</span>
                        <span class="text-slate-300">·</span>
                        <span class="font-semibold text-slate-700">R{{ number_format($service->price, 2) }}</span>
                    </div>
                </button>
            @endforeach
        </div>

        {{-- Sticky summary + continue --}}
        <div x-show="selected.length > 0"
             x-cloak
             x-transition
             class="sticky bottom-4 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 flex items-center justify-between gap-4">
            <div class="text-sm text-slate-600 space-y-0.5">
                <p class="font-semibold text-slate-900" x-text="`${selected.length} service${selected.length > 1 ? 's' : ''} selected`"></p>
                <p class="text-slate-400" x-text="`${totalDuration} min · R${totalPrice}`"></p>
            </div>
            <form method="GET" :action="'{{ route('book.service', $slug) }}'">
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="service_ids[]" :value="id">
                </template>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-xl transition">
                    Continue &rarr;
                </button>
            </form>
        </div>
    @endif
</div>

@push('scripts')
<script>
function servicePicker() {
    return {
        selected: [],
        totalDuration: 0,
        totalPrice: '0.00',
        services: @json($servicesJson),

        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) {
                this.selected.push(id);
            } else {
                this.selected.splice(idx, 1);
            }
            this.recalculate();
        },

        recalculate() {
            let mins  = 0;
            let price = 0;
            this.selected.forEach(id => {
                const s = this.services.find(s => s.id === id);
                if (s) { mins += s.duration_minutes; price += s.price; }
            });
            this.totalDuration = mins;
            this.totalPrice    = price.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
    };
}
</script>
@endpush
@endsection