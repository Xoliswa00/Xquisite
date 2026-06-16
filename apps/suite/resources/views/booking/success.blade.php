@extends('layouts.booking')

@section('content')
<div class="text-center space-y-8 py-8">

    <div class="flex justify-center">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
    </div>

    <div>
        <h1 class="text-3xl font-bold text-slate-900">You're booked!</h1>
        <p class="text-slate-500 mt-2">We'll see you on {{ $appointment->scheduled_at->format('l, d F Y') }}.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-left space-y-4 max-w-sm mx-auto">
        <div class="text-sm space-y-4">

            {{-- Services list --}}
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold mb-2">Services</p>
                <div class="divide-y divide-slate-100">
                    @foreach($appointment->services as $service)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="font-medium text-slate-900">{{ $service->name }}</span>
                            <span class="text-slate-400 text-xs">{{ $service->pivot->duration_minutes ?? $service->duration_minutes }} min</span>
                        </div>
                    @endforeach
                </div>
                @php
                    $successTotal = (float)$appointment->services->sum(fn($s) => $s->pivot->price_at_booking ?? $s->price);
                    if ($appointment->combo_price) {
                        $successComboIds = \App\Models\ServiceCombo::with('services')->find($appointment->combo_id)?->services->pluck('id')->all() ?? [];
                        $successExtras   = $appointment->services->filter(fn($s) => !in_array($s->id, $successComboIds))->sum(fn($s) => $s->pivot->price_at_booking ?? $s->price);
                        $successTotal    = (float)$appointment->combo_price + $successExtras;
                    }
                    $successTotal -= (float)($appointment->promo_discount ?? 0);
                @endphp
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 font-semibold">
                    <span class="text-slate-700">Total</span>
                    <span class="text-indigo-600">R{{ number_format($successTotal, 2) }} · {{ $appointment->duration_minutes }} min</span>
                </div>
                @if($appointment->promo_discount)
                    <p class="text-xs text-emerald-600 text-right">Promo {{ $appointment->promo_code }} saved you R{{ number_format($appointment->promo_discount, 2) }}</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Staff</p>
                    @if($appointment->isUnassigned())
                        <p class="text-slate-400 text-sm mt-0.5 italic">Assigned on confirmation</p>
                    @else
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $appointment->staff->name }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Date</p>
                    <p class="font-semibold text-slate-900 mt-0.5">{{ $appointment->scheduled_at->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Time</p>
                    <p class="font-semibold text-slate-900 mt-0.5">{{ $appointment->scheduled_at->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Ends</p>
                    <p class="font-semibold text-slate-900 mt-0.5">
                        {{ $appointment->scheduled_at->copy()->addMinutes($appointment->duration_minutes)->format('H:i') }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('book.my-bookings', $slug) }}"
           class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition">
            View my bookings
        </a>
        <a href="{{ route('book.index', $slug) }}"
           class="px-6 py-3 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold rounded-xl transition">
            Book another
        </a>
    </div>

</div>
@endsection