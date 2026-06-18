<x-guest-layout>

    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-[#0078D4]/10 border border-[#0078D4]/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-[#0078D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-[#D4AF37]" style="font-family:'Montserrat',sans-serif">Check your email</h1>
        <p class="text-sm text-[#2D3748]/60 mt-2 max-w-sm mx-auto">
            We've sent a verification link to your email address. Click the link to activate your account.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm text-center">
            A new verification link has been sent to your email.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full bg-white hover:bg-[#F5F7FA] border border-gray-200 text-[#2D3748] py-3 rounded-xl text-sm transition-colors">
                Sign Out
            </button>
        </form>
    </div>

</x-guest-layout>
