@extends('layouts.booking')

@section('content')
<div class="space-y-8">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">My Bookings</h1>
            <p class="text-slate-500 text-sm mt-0.5">{{ auth('customer')->user()->name }}</p>
        </div>
        <a href="{{ route('book.index', $slug) }}"
           class="bg-[#0078D4] hover:bg-[#0078D4] text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            + Book again
        </a>
    </div>

    {{-- Upcoming --}}
    <div>
        <h2 class="text-base font-semibold text-slate-700 mb-3">Upcoming</h2>

        @if($upcoming->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                No upcoming appointments.
                <a href="{{ route('book.index', $slug) }}" class="text-[#0078D4] hover:underline ml-1">Book one now &rarr;</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($upcoming as $appt)
                @php $serviceNames = $appt->services->pluck('name')->join(', '); @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3"
                     x-data="{ showUpload: false }">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 truncate">{{ $serviceNames }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">
                                with {{ $appt->staff->name }}
                                &middot; {{ $appt->scheduled_at->format('d M Y, H:i') }}
                                &middot; {{ $appt->duration_minutes }} min
                            </p>
                            @if($appt->payment_proof_path)
                                <p class="mt-1 flex items-center gap-1 text-xs text-emerald-600 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Proof of payment submitted
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $appt->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                            </span>
                            <div class="flex items-center gap-3">
                                @if(!$appt->payment_proof_path)
                                    <button type="button" @click="showUpload = !showUpload"
                                            class="text-xs text-[#0078D4] hover:text-[#0065B8] font-medium">
                                        Upload proof of payment
                                    </button>
                                @endif
                                @if($appt->scheduled_at->diffInHours(now(), false) < -2)
                                    <form method="POST" action="{{ route('book.cancel', [$slug, $appt]) }}"
                                          onsubmit="return confirm('Cancel this appointment?')">
                                        @csrf @method('PATCH')
                                        <button class="text-xs text-red-500 hover:text-red-700">Cancel</button>
                                    </form>
                                    <a href="{{ route('book.edit', [$slug, $appt]) }}"
                                       class="text-xs text-[#0078D4] hover:text-[#0078D4]">Reschedule &rarr;</a>
                                @else
                                    <span class="text-xs text-slate-300" title="Cancellation window has passed">Cannot cancel</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Inline proof upload --}}
                    <div x-show="showUpload" x-cloak x-transition class="border-t border-slate-100 pt-3">
                        <form method="POST"
                              action="{{ route('book.payment-proof', [$slug, $appt]) }}"
                              enctype="multipart/form-data"
                              class="flex items-center gap-3 flex-wrap">
                            @csrf
                            @if($errors->has('payment_proof'))
                                <p class="w-full text-xs text-red-500">{{ $errors->first('payment_proof') }}</p>
                            @endif
                            <input type="file" name="payment_proof" accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   class="flex-1 text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <button type="submit"
                                    class="px-4 py-1.5 bg-[#0078D4] text-white text-xs font-semibold rounded-lg whitespace-nowrap">
                                Upload
                            </button>
                        </form>
                        <p class="text-xs text-slate-400 mt-1">PDF, JPG, PNG or WebP &middot; max 8 MB</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Past --}}
    @if($past->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-slate-700 mb-3">Past</h2>
        <div class="space-y-2">
            @foreach($past as $appt)
            @php $serviceNames = $appt->services->pluck('name')->join(', '); @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center justify-between opacity-70">
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900 truncate">{{ $serviceNames }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        with {{ $appt->staff->name }}
                        &middot; {{ $appt->scheduled_at->format('d M Y, H:i') }}
                    </p>
                </div>
                <span class="ml-4 shrink-0 px-2 py-0.5 rounded-full text-xs
                    @if($appt->status === 'completed') bg-slate-100 text-slate-600
                    @elseif($appt->status === 'cancelled') bg-red-100 text-red-600
                    @else bg-slate-100 text-slate-500 @endif">
                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
