@extends('layouts.booking')

@section('content')
<div class="space-y-8">

    <nav class="flex items-center gap-2 text-sm text-slate-400">
        <a href="{{ route('book.index', $slug) }}" class="hover:text-indigo-600">Services</a>
        <span>&rsaquo;</span>
        <a href="{{ route('book.service', [$slug, $service]) }}" class="hover:text-indigo-600">{{ $service->name }}</a>
        <span>&rsaquo;</span>
        <span class="text-slate-700 font-medium">Confirm</span>
    </nav>

    <h1 class="text-2xl font-bold text-slate-900">Confirm your booking</h1>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">Service</p>
                <p class="font-semibold text-slate-900 mt-1">{{ $service->name }}</p>
                <p class="text-slate-500">{{ $service->duration_minutes }} min &middot; R{{ number_format($service->price, 2) }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-xs uppercase font-semibold">Staff</p>
                <p class="text-slate-400 mt-1 italic text-sm">Assigned on confirmation</p>
            </div>
            <div class="col-span-2">
                <p class="text-slate-400 text-xs uppercase font-semibold">Date &amp; Time</p>
                <p class="font-semibold text-slate-900 mt-1">{{ $slot->format('l, d F Y') }}</p>
                <p class="text-slate-500">{{ $slot->format('H:i') }} &ndash; {{ $slot->copy()->addMinutes($service->duration_minutes)->format('H:i') }}</p>
            </div>
        </div>
    </div>

    @if($customer)
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-700">
            Booking as <strong>{{ $customer->name }}</strong> ({{ $customer->email }})
        </div>

        <form method="POST" action="{{ route('book.store', $slug) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
                    <textarea name="notes" rows="3" placeholder="Any special requests or information&hellip;"
                              class="w-full border-slate-300 rounded-xl text-sm"></textarea>
                </div>
                <button type="submit"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition text-lg">
                    Confirm Booking
                </button>
            </div>
        </form>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
            You need an account to complete your booking. Your slot is held while you sign in.
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <a href="{{ route('book.login', $slug) }}"
               class="block text-center py-3 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl transition">
                Sign in to existing account
            </a>
            <a href="{{ route('book.register', $slug) }}"
               class="block text-center py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition">
                Create a new account
            </a>
        </div>
    @endif

</div>
@endsection
