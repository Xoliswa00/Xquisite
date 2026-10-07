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
            Ask {{ $tenant->name }} to send you a setup link. It opens a page where you choose your own
            email and password, and your past bookings stay on your account.
        </p>
        <p>
            They send the link to the cell number they have on record for you, so nobody else can set up
            a login in your name.
        </p>

        @if($tenant->phone)
            <x-whatsapp-link :phone="$tenant->phone"
                :message="'Hi ' . $tenant->name . ', please send me the link to set up my online login.'"
                class="w-full justify-center py-3 bg-[#0078D4] hover:bg-[#0065B8] hover:text-white text-white font-semibold rounded-xl transition">Ask for my setup link on WhatsApp</x-whatsapp-link>
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
