<x-guest-layout>
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-5">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h2 class="text-xl font-bold text-gray-900 mb-2">Quote Accepted!</h2>
            <p class="text-gray-500 text-sm mb-6">
                Your booking for <strong>{{ $quote->title }}</strong> is confirmed once the deposit is paid.
            </p>

            @if ($deposit)
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-6">
                <p class="text-sm text-indigo-600 font-medium">Deposit Amount</p>
                <p class="text-3xl font-bold text-indigo-700 my-1">R{{ number_format($deposit->amount, 2) }}</p>
                <p class="text-xs text-indigo-500">Due: {{ $deposit->due_date->format('d F Y') }}</p>
            </div>

            <div class="text-left space-y-3 text-sm text-gray-600 mb-6">
                <p class="font-semibold text-gray-900">Pay via EFT:</p>
                <div class="bg-gray-50 rounded-lg p-4 space-y-1 font-mono text-xs">
                    <p><span class="text-gray-400">Bank:</span> {{ config('app.bank_name', 'First National Bank') }}</p>
                    <p><span class="text-gray-400">Account:</span> {{ config('app.bank_account', 'Contact us for banking details') }}</p>
                    <p><span class="text-gray-400">Branch:</span> {{ config('app.bank_branch', '') }}</p>
                    <p><span class="text-gray-400">Reference:</span> <strong>{{ $quote->reference }}</strong></p>
                </div>
                <p class="text-xs text-gray-400">Please use your quote reference as the payment reference so we can match it quickly.</p>
            </div>
            @endif

            <a href="{{ route('public.quotes.show', [$quote, $token]) }}"
               class="text-sm text-indigo-600 hover:text-indigo-800">← View full quote</a>
        </div>

        <p class="text-xs text-gray-400 text-center mt-6">Powered by Xquisite Technologies (Pty) Ltd</p>
    </div>
</div>
</x-guest-layout>
