<x-guest-layout>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#D4AF37]" style="font-family:'Montserrat',sans-serif">Welcome back</h1>
        <p class="text-sm text-[#2D3748]/60 mt-1">Sign in to your Xquisite Creations account</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-[#002B5B] mb-1">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@yourbusiness.co.za"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] placeholder-gray-400 @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-[#002B5B]">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-[#0078D4] hover:text-[#0065B8]">Forgot password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] @error('password') border-red-400 @enderror">
            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded bg-white border-gray-300 text-[#0078D4] focus:ring-[#0078D4]">
            <label for="remember_me" class="text-sm text-[#2D3748]/70">Remember me for 30 days</label>
        </div>

        <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold py-3 rounded-xl text-sm transition-colors">
            Sign In
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-[#2D3748]/60">
            Don't have an account? <a href="{{ route('register') }}" class="text-[#0078D4] hover:text-[#0065B8] font-medium">Start free trial</a>
        </p>
    @endif

</x-guest-layout>
