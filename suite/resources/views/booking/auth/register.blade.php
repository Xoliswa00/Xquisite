@extends('layouts.booking')

@section('content')
<div class="max-w-md mx-auto space-y-6">

    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-900">Create your account</h1>
        <p class="text-slate-500 mt-1 text-sm">
            Already have one?
            <a href="{{ route('book.login', $slug) }}" class="text-[#0078D4] hover:underline">Sign in</a>
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8">
        <form method="POST" action="{{ route('book.register.post', $slug) }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full border-slate-300 rounded-xl @error('name') border-red-400 @enderror">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border-slate-300 rounded-xl @error('email') border-red-400 @enderror">
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone (optional)</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="w-full border-slate-300 rounded-xl">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border-slate-300 rounded-xl @error('password') border-red-400 @enderror">
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border-slate-300 rounded-xl">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl transition">
                Create account & continue
            </button>
        </form>
    </div>

    <p class="text-center text-sm">
        <a href="{{ route('book.index', $slug) }}" class="text-slate-400 hover:text-slate-600">&larr; Back to services</a>
    </p>

</div>
@endsection


