<x-guest-layout>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#D4AF37]" style="font-family:'Montserrat',sans-serif">Set new password</h1>
        <p class="text-sm text-[#2D3748]/60 mt-1">Choose a strong password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium text-[#002B5B] mb-1">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-[#002B5B] mb-1">New Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-[#002B5B] mb-1">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4]">
        </div>

        <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold py-3 rounded-xl text-sm transition-colors">
            Reset Password
        </button>
    </form>

</x-guest-layout>
