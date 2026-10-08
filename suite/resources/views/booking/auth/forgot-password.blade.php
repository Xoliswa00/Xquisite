@extends('layouts.booking')

@section('content')
<div class="max-w-md mx-auto space-y-6">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-900">Forgot your password?</h1>
        <p class="text-slate-500 mt-1 text-sm">Enter your email and we&rsquo;ll send you a reset link.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8">
        <form method="POST" action="{{ route('book.password.email', $slug) }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border-slate-300 rounded-xl @error('email') border-red-400 @enderror">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl transition">
                Send Reset Link
            </button>
        </form>

        <div class="border-t border-slate-200 mt-6 pt-5 space-y-3 text-sm text-slate-600">
            <p>No email address on your account? Ask {{ $tenant->name }} to send you a new login link.</p>
            @if($tenant->phone)
                <x-whatsapp-link :phone="$tenant->phone"
                    :message="'Hi ' . $tenant->name . ', I have forgotten my password. Please send me a new login link. My name is '"
                    class="w-full justify-center py-3 border border-slate-300 hover:border-slate-400 text-slate-900 font-semibold rounded-xl transition">Ask on WhatsApp</x-whatsapp-link>
            @endif
        </div>
    </div>

    <p class="text-center text-sm">
        <a href="{{ route('book.login', $slug) }}" class="text-slate-400 hover:text-slate-600">&larr; Back to sign in</a>
    </p>
</div>
@endsection
