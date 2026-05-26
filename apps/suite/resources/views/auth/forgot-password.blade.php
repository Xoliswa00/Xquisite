<x-guest-layout>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Reset your password</h1>
        <p class="text-sm text-slate-400 mt-1">Enter your email and we'll send you a reset link.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}" required autofocus
                   placeholder="you@yourbusiness.co.za"
                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder-slate-500 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
            Send Reset Link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400">
        <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300">← Back to sign in</a>
    </p>

</x-guest-layout>
