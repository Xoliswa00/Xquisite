@extends('layouts.booking')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-900">Set up your login</h1>
        <p class="text-slate-500 mt-1 text-sm">
            Already a client of {{ $tenant->name }} but never signed in online?
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8 space-y-4 text-sm text-slate-600">
        <p>
            Ask {{ $tenant->name }} for your setup link. It opens a page where you choose your own
            password, and your past bookings stay on your account.
        </p>
        <p>
            They send it to the cell number they have for you, usually during business hours, so
            nobody else can set up a login in your name.
        </p>

        <form method="POST" action="{{ route('book.claim.request', $slug) }}" class="space-y-3">
            @csrf
            <div>
                <label for="contact" class="block text-sm font-medium text-slate-700 mb-1">Your cell number or email address</label>
                <input type="text" id="contact" name="contact" value="{{ old('contact') }}" required autocomplete="tel"
                       class="w-full border-slate-300 rounded-xl @error('contact') border-red-400 @enderror">
                <p class="text-xs text-slate-500 mt-1">The one {{ $tenant->name }} has for you, like 082 123 4567.</p>
                @error('contact') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl transition">
                Ask {{ $tenant->name }} for my link
            </button>
        </form>

        @if($tenant->phone)
            <p class="text-slate-500">
                In a hurry? WhatsApp
                <x-whatsapp-link :phone="$tenant->phone"
                    :message="'Hi ' . $tenant->name . ', please send me the link to set up my online login. My name is '"
                    class="font-medium text-slate-700" />
                or ask at your next visit.
            </p>
        @else
            <p class="text-slate-500">Or ask at your next visit.</p>
        @endif
    </div>

    <div class="text-center space-y-2 text-sm">
        <p>
            <a href="{{ route('book.login', $slug) }}" class="text-[#0078D4] hover:underline">&larr; Back to sign in</a>
        </p>
        <p class="text-slate-500">
            New customer?
            <a href="{{ route('book.register', $slug) }}" class="text-[#0078D4] hover:underline">Create an account</a>
        </p>
    </div>

</div>
@endsection
