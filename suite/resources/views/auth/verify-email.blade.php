<x-guest-layout>

    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-indigo-600/20 border border-indigo-500/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Check your email</h1>
        <p class="text-sm text-slate-400 mt-2 max-w-sm mx-auto">
            We've sent a verification link to your email address. Click the link to activate your account.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-900/40 border border-emerald-700 text-emerald-300 text-sm text-center">
            A new verification link has been sent to your email.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 py-3 rounded-xl text-sm transition-colors">
                Sign Out
            </button>
        </form>
    </div>

</x-guest-layout>
