@extends('layouts.booking')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-900">{{ $customer->password ? 'Choose a new password' : 'Set up your login' }}</h1>
        <p class="text-slate-500 mt-1 text-sm">
            Hi <span class="font-semibold text-slate-700">{{ $customer->name }}</span>.
            @if($customer->password)
                Choose a new password for your login with {{ $tenant->name }}. Your old one stops working.
            @else
                Choose a password to finish setting up your login with {{ $tenant->name }}.
            @endif
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8">
        <form method="POST" action="{{ $submitUrl }}" class="space-y-5">
            @csrf

            {{-- Phone (read-only): this is what they sign in with --}}
            @if($customer->phone)
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cell number</label>
                <input type="text" value="{{ $customer->phone }}" disabled
                       class="w-full border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed">
                <p class="text-xs text-slate-500 mt-1">You sign in with this number and your password.</p>
            </div>
            @endif

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Create a password <span class="text-red-500">*</span></label>
                <input type="password" id="password" name="password" required minlength="8" autofocus autocomplete="new-password"
                       class="w-full border-slate-300 rounded-xl @error('password') border-red-400 @enderror">
                <p class="text-xs text-slate-500 mt-1">At least 8 characters.</p>
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Type it again <span class="text-red-500">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password"
                       class="w-full border-slate-300 rounded-xl">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                    Email address
                    @if($customer->phone)
                        <span class="font-normal text-slate-500">(optional)</span>
                    @else
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $customer->email) }}" autocomplete="email"
                       @unless($customer->phone) required @endunless
                       class="w-full border-slate-300 rounded-xl @error('email') border-red-400 @enderror">
                <p class="text-xs text-slate-500 mt-1">
                    @if($customer->phone)
                        Add one if you have it. It lets you reset a forgotten password yourself.
                    @else
                        You sign in with this address and your password.
                    @endif
                </p>
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                    class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl transition">
                Save my login
            </button>
        </form>
    </div>

    <p class="text-center text-sm">
        <a href="{{ route('book.login', $slug) }}" class="text-[#0078D4] hover:underline">&larr; Back to sign in</a>
    </p>

</div>
@endsection
