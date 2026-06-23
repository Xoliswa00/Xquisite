@extends('layouts.booking')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-900">Claim your account</h1>
        <p class="text-slate-500 mt-1 text-sm">
            Already a client of {{ $tenant->name }}? Enter your cell number and we'll find your record.
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8">
        <form method="POST" action="{{ route('book.claim.lookup', $slug) }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cell phone number</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required autofocus
                       placeholder="e.g. 082 123 4567"
                       class="w-full border-slate-300 rounded-xl @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl transition">
                Find my account
            </button>
        </form>
    </div>

    <div class="text-center space-y-2 text-sm">
        <p>
            <a href="{{ route('book.login', $slug) }}" class="text-[#0078D4] hover:underline">&larr; Back to sign in</a>
        </p>
        <p class="text-slate-400">
            New customer?
            <a href="{{ route('book.register', $slug) }}" class="text-[#0078D4] hover:underline">Create an account</a>
        </p>
    </div>

</div>
@endsection
