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

        @if($tenant->phone)
            <x-whatsapp-link :phone="$tenant->phone"
                :message="'Hi ' . $tenant->name . ', please send me the link to set up my online login. My name is '"
                class="w-full justify-center py-3 border border-slate-300 hover:border-slate-400 text-slate-900 font-semibold rounded-xl transition">Ask for my setup link on WhatsApp</x-whatsapp-link>
            <p class="text-slate-500">No reply? Call {{ $tenant->phone }} or ask at your next visit.</p>
        @elseif($tenant->email)
            <p>Email <a href="mailto:{{ $tenant->email }}" class="text-[#0078D4] hover:underline font-medium">{{ $tenant->email }}</a> or ask at your next visit.</p>
        @else
            <p class="font-medium text-slate-700">Ask for your setup link at your next visit.</p>
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
