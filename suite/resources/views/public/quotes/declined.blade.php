<x-guest-layout>
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl border border-gray-200 p-8 text-center">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Quote Declined</h2>
        <p class="text-gray-500 text-sm">You've declined the quote for <strong>{{ $quote->title }}</strong>.</p>
        <p class="text-gray-400 text-sm mt-3">The business has been notified. If you'd like to discuss alternatives, please contact them directly.</p>
        <p class="text-xs text-gray-400 mt-8">Powered by Xquisite Technologies (Pty) Ltd</p>
    </div>
</div>
</x-guest-layout>
