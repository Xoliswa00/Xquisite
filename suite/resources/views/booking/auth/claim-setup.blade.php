@extends('layouts.booking')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-900">Set up your login</h1>
        <p class="text-slate-500 mt-1 text-sm">
            We found your record, <span class="font-semibold text-slate-700">{{ $customer->name }}</span>.
            Add an email and password to activate your account.
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8">
        <form method="POST" action="{{ route('book.claim.complete', $slug) }}" class="space-y-5">
            @csrf

            {{-- Name (read-only — already on file) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full name</label>
                <input type="text" value="{{ $customer->name }}" disabled
                       class="w-full border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed">
            </div>

            {{-- Phone (read-only) --}}
            @if($customer->phone)
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input type="text" value="{{ $customer->phone }}" disabled
                       class="w-full border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed">
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border-slate-300 rounded-xl @error('email') border-red-400 @enderror">
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Create a password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="w-full border-slate-300 rounded-xl @error('password') border-red-400 @enderror">
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full border-slate-300 rounded-xl">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl transition">
                Activate my account
            </button>
        </form>
    </div>

    <p class="text-center text-sm">
        <a href="{{ route('book.claim', $slug) }}" class="text-slate-400 hover:text-slate-600">&larr; Use a different number</a>
    </p>

</div>
@endsection
