<x-guest-layout>
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-8">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Quotation</p>
            <h1 class="text-2xl font-bold text-gray-900">{{ $quote->title }}</h1>
            <p class="text-gray-500 mt-1">{{ $quote->reference }}</p>
            @if ($quote->valid_until)
                <p class="text-sm text-gray-400 mt-1">Valid until {{ $quote->valid_until->format('d F Y') }}</p>
            @endif
        </div>

        {{-- Line items --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-5">
            <table class="min-w-full divide-y divide-gray-100 summary-on-mobile">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($quote->line_items as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item['name'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ $item['qty'] }} {{ $item['unit'] ?? '' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 text-right">R{{ number_format($item['unit_price'], 2) }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">R{{ number_format($item['total'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-sm text-gray-500 text-right">Subtotal</td>
                        <td class="px-6 py-3 text-sm text-gray-900 text-right">R{{ number_format($quote->subtotal, 2) }}</td>
                    </tr>
                    @if ($quote->tax_rate > 0)
                    <tr>
                        <td colspan="3" class="px-6 py-2 text-sm text-gray-500 text-right">VAT ({{ $quote->tax_rate }}%)</td>
                        <td class="px-6 py-2 text-sm text-gray-900 text-right">R{{ number_format($quote->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-base font-bold text-gray-900 text-right">Total</td>
                        <td class="px-6 py-4 text-xl font-bold text-gray-900 text-right">R{{ number_format($quote->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            @if ($quote->notes)
            <div class="px-6 py-4 border-t border-gray-100 text-sm text-gray-500 whitespace-pre-line">
                {{ $quote->notes }}
            </div>
            @endif
        </div>

        {{-- Deposit highlight --}}
        <div class="bg-[#F0F7FF] border border-[#DCEEFA] rounded-xl p-5 mb-5 text-center">
            <p class="text-sm text-[#002B5B] font-medium">To confirm this booking, a deposit of</p>
            <p class="text-3xl font-bold text-[#002B5B] my-1">R{{ number_format($quote->depositAmount(), 2) }}</p>
            <p class="text-xs text-[#0078D4]">({{ $quote->deposit_percentage }}% of total) is required to secure your date.</p>
        </div>

        @if (in_array($quote->status, ['draft', 'sent']))
        {{-- Accept / Decline --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <form method="POST" action="{{ route('public.quotes.accept', [$quote, $token]) }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full py-3.5 bg-[#0078D4] hover:bg-[#0078D4] text-white font-semibold rounded-xl transition">
                    Accept & Pay Deposit
                </button>
            </form>
            <form method="POST" action="{{ route('public.quotes.decline', [$quote, $token]) }}">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-3.5 border border-gray-200 text-gray-500 hover:text-gray-700 rounded-xl transition">
                    Decline
                </button>
            </form>
        </div>

        <p class="text-xs text-gray-400 text-center mt-4">
            By accepting you agree to the payment terms above. The balance is due as specified.
        </p>

        @elseif ($quote->status === 'accepted')
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 text-center">
                <p class="text-emerald-700 font-semibold">✓ You've accepted this quote.</p>
                <p class="text-sm text-emerald-600 mt-1">The business has been notified. Check your email for payment details.</p>
            </div>
        @elseif ($quote->status === 'declined')
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-center">
                <p class="text-gray-600">You declined this quote.</p>
            </div>
        @endif

        <p class="text-xs text-gray-400 text-center mt-8">Powered by Xquisite Suite · Xquisite Technologies (Pty) Ltd</p>
    </div>
</div>
</x-guest-layout>
