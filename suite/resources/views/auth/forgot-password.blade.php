<x-guest-layout>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#D4AF37]" style="font-family:'Montserrat',sans-serif">Reset your password</h1>
        <p class="text-sm text-[#2D3748]/60 mt-1">Enter your email and we'll send you a reset link.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-[#002B5B] mb-1">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="you@yourbusiness.co.za"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] placeholder-gray-400 @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold py-3 rounded-xl text-sm transition-colors">
            Send Reset Link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-[#2D3748]/60">
        <a href="{{ route('login') }}" class="text-[#0078D4] hover:text-[#0065B8]">&larr; Back to sign in</a>
    </p>

</x-guest-layout>
