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
                    <span class="text-[#0078D4]">R{{ number_format($successTotal, 2) }} · {{ $appointment->duration_minutes }} min</span>
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

    {{-- Proof of payment upload --}}
    @if(!$appointment->payment_proof_path)
    <div class="max-w-sm mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 text-left"
             x-data="{ dragging: false }">
            <p class="text-sm font-semibold text-slate-700 mb-0.5">Paid via EFT?</p>
            <p class="text-xs text-slate-400 mb-4">Upload your proof of payment so we can confirm your booking faster.</p>

            @if(session('proof_uploaded'))
                <div class="flex items-center gap-2 text-emerald-600 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Proof uploaded — we'll confirm your booking shortly.
                </div>
            @else
            <form method="POST"
                  action="{{ route('book.payment-proof', [$slug, $appointment]) }}"
                  enctype="multipart/form-data"
                  class="space-y-3">
                @csrf
                @if($errors->has('payment_proof'))
                    <p class="text-xs text-red-500">{{ $errors->first('payment_proof') }}</p>
                @endif

                <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl px-4 py-6 cursor-pointer transition-colors"
                       :class="dragging ? 'border-[#0078D4] bg-blue-50' : 'border-slate-200 hover:border-slate-300'"
                       @dragover.prevent="dragging = true"
                       @dragleave="dragging = false"
                       @drop.prevent="dragging = false; $refs.proofFile.files = $event.dataTransfer.files; $refs.proofFile.dispatchEvent(new Event('change'))">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span class="text-xs text-slate-400" x-text="'Drop file here or click to browse'"></span>
                    <span class="text-xs text-slate-300">PDF, JPG, PNG or WebP · max 8 MB</span>
                    <input type="file" name="payment_proof" x-ref="proofFile" accept=".pdf,.jpg,.jpeg,.png,.webp"
                           class="sr-only"
                           @change="$el.closest('label').querySelector('span').textContent = $el.files[0]?.name || 'Drop file here or click to browse'">
                </label>

                <button type="submit"
                        class="w-full py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-xl transition">
                    Upload proof of payment
                </button>
            </form>
            @endif
        </div>
    </div>
    @else
    <div class="max-w-sm mx-auto">
        <div class="flex items-center gap-3 bg-white rounded-2xl border border-emerald-200 px-5 py-4">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm">
                <p class="font-semibold text-slate-700">Proof of payment uploaded</p>
                <p class="text-slate-400 text-xs">{{ $appointment->payment_proof_name }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('book.my-bookings', $slug) }}"
           class="px-6 py-3 bg-[#0078D4] hover:bg-[#0078D4] text-white font-semibold rounded-xl transition">
            View my bookings
        </a>
        <a href="{{ route('book.index', $slug) }}"
           class="px-6 py-3 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold rounded-xl transition">
            Book another
        </a>
    </div>

</div>
@endsection