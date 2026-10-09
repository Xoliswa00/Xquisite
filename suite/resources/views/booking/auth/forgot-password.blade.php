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
        </div>
    </div>

    <p class="text-center text-sm">
        <a href="{{ route('book.login', $slug) }}" class="text-slate-400 hover:text-slate-600">&larr; Back to sign in</a>
    </p>
</div>
@endsection
