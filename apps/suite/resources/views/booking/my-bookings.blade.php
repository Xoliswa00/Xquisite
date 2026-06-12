@extends('layouts.booking')

@section('content')
<div class="space-y-8">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">My Bookings</h1>
            <p class="text-slate-500 text-sm mt-0.5">{{ auth('customer')->user()->name }}</p>
        </div>
        <a href="{{ route('book.index', $slug) }}"
           class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            + Book again
        </a>
    </div>

    {{-- Upcoming --}}
    <div>
        <h2 class="text-base font-semibold text-slate-700 mb-3">Upcoming</h2>

        @if($upcoming->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                No upcoming appointments.
                <a href="{{ route('book.index', $slug) }}" class="text-indigo-600 hover:underline ml-1">Book one now â†’</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($upcoming as $appt)
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center justify-between">
                        <div>
@php
    $serviceNames = $appt->services->pluck('name')->join(', ');
@endphp

<p class="font-semibold text-slate-900">
    {{ $serviceNames }}
</p>                            <p class="text-sm text-slate-500 mt-0.5">
                                with {{ $appt->staff->name }}
                                Â· {{ $appt->scheduled_at->format('d M Y, H:i') }}
                                Â· {{ $appt->duration_minutes }} min
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $appt->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($appt->status) }}
                            </span>
                            @if($appt->scheduled_at->diffInHours(now(), false) < -2)
                                <form method="POST" action="{{ route('book.cancel', [$slug, $appt]) }}"
                                      onsubmit="return confirm('Cancel this appointment?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-xs text-red-500 hover:text-red-700">Cancel</button>
                                </form>

                                <a read_exif_datata href="{{ route('book.edit', [$slug, $appt]) }}"
                                   class="text-xs text-indigo-600 hover:text-indigo-500">
                                    Reschedule →
                                </a>
                            @else
                                <span class="text-xs text-slate-300" title="Cancellation window has passed">Cannot cancel</span>
                            @endif
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
                <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center justify-between opacity-70">
                    <div>
                     </p>@php
    $serviceNames = $appt->services->pluck('name')->join(', ');
@endphp

<p class="font-semibold text-slate-900">
    {{ $serviceNames }}
</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            with {{ $appt->staff->name }}
                            Â· {{ $appt->scheduled_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs
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

