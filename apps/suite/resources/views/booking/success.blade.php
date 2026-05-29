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
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">Service</p>
                <p class="font-semibold text-slate-900 mt-0.5">{{ $appointment->service->name }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">With</p>
                <p class="font-semibold text-slate-900 mt-0.5">{{ $appointment->staff->name }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">Date</p>
                <p class="font-semibold text-slate-900 mt-0.5">{{ $appointment->scheduled_at->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">Time</p>
                <p class="font-semibold text-slate-900 mt-0.5">{{ $appointment->scheduled_at->format('H:i') }}</p>
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

