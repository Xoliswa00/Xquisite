@extends('property.portal.layout')

@section('content')
<div class="max-w-md mx-auto space-y-6">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-900">Forgot your password?</h1>
        <p class="text-slate-500 mt-1 text-sm">Enter your email and we&rsquo;ll send you a reset link.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8">
        <form method="POST" action="{{ route('rent.password.email', $slug) }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border-slate-300 rounded-xl @error('email') border-red-400 @enderror">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl">
                Send Reset Link
            </button>
        </form>
    </div>

    <p class="text-center text-sm">
        <a href="{{ route('rent.login', $slug) }}" class="text-slate-400 hover:text-slate-600">&larr; Back to sign in</a>
    </p>
</div>
@endsection
