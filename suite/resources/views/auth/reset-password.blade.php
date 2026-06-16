<x-guest-layout>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Set new password</h1>
        <p class="text-sm text-slate-400 mt-1">Choose a strong password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1">New Password</label>
            <input id="password" type="password" name="password"
                   required autocomplete="new-password"
                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   required autocomplete="new-password"
                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
            Reset Password
        </button>
    </form>

</x-guest-layout>
