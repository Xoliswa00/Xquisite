<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('quotes.index') }}" class="text-slate-500 hover:text-slate-400">← Quotes</a>
            <span class="text-slate-500">/</span>
            <h2 class="text-xl font-semibold">{{ $quote->reference }} — {{ $quote->title }}</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

        @if (session('success'))
            <div class="p-4 bg-emerald-900/50 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 bg-red-900/50 border border-red-700 text-red-300 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        {{-- Status bar --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-5 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                @php
                    $colours = ['draft'=>'bg-slate-800/50 text-slate-400','sent'=>'bg-blue-100 text-blue-700',
                                'accepted'=>'bg-emerald-100 text-emerald-700','declined'=>'bg-red-100 text-red-700',
                                'expired'=>'bg-amber-100 text-amber-700','converted'=>'bg-[#E8F2FA] text-[#002B5B]'];
                @endphp
                <span class="text-sm px-3 py-1 rounded-full font-medium {{ $colours[$quote->status] ?? '' }}">
                    {{ ucfirst($quote->status) }}
                </span>
                <span class="text-sm text-slate-500">
                    {{ $quote->customer?->name ?? $quote->client_email ?? 'No client' }}
                </span>
                @if ($quote->valid_until)
                    <span class="text-sm text-slate-500">· Valid until {{ $quote->valid_until->format('d M Y') }}</span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if (in_array($quote->status, ['draft', 'sent']))
                <form method="POST" action="{{ route('quotes.send', $quote) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-sm bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg font-medium">
                        {{ $quote->status === 'sent' ? 'Resend' : 'Send to Client' }}
                    </button>
                </form>
                @endif
                <a href="{{ route('public.quotes.show', [$quote, $quote->acceptToken()]) }}"
                   target="_blank"
                   class="px-3 py-1.5 text-sm border border-slate-700 text-slate-400 hover:border-slate-600 rounded-lg">
                    Preview →
                </a>
                @if (!in_array($quote->status, ['accepted', 'converted']))
                <form method="POST" action="{{ route('quotes.destroy', $quote) }}"
                      onsubmit="return confirm('Delete this quote?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 px-2 py-1.5">Delete</button>
                </form>
                @endif
            </div>
        </div>

        {{-- Line items --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-700/50">
                <h3 class="font-semibold text-[#D4AF37]">{{ $quote->title }}</h3>
            </div>
            <table class="min-w-full divide-y divide-slate-700/50 summary-on-mobile">
                <thead class="bg-slate-900">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500">Description</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500">Qty</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500">Unit Price</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach ($quote->line_items as $item)
                    <tr>
                        <td class="px-5 py-3 text-sm text-white">{{ $item['name'] }}</td>
                        <td class="px-5 py-3 text-sm text-slate-500 text-right">
                            {{ $item['qty'] }} {{ $item['unit'] ?? '' }}
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-500 text-right">R{{ number_format($item['unit_price'], 2) }}</td>
                        <td class="px-5 py-3 text-sm font-medium text-white text-right">R{{ number_format($item['total'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-900">
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-sm text-slate-500 text-right">Subtotal</td>
                        <td class="px-5 py-3 text-sm text-white text-right">R{{ number_format($quote->subtotal, 2) }}</td>
                    </tr>
                    @if ($quote->tax_rate > 0)
                    <tr>
                        <td colspan="3" class="px-5 py-2 text-sm text-slate-500 text-right">Tax ({{ $quote->tax_rate }}%)</td>
                        <td class="px-5 py-2 text-sm text-white text-right">R{{ number_format($quote->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-sm font-bold text-white text-right">Total</td>
                        <td class="px-5 py-3 text-base font-bold text-white text-right">R{{ number_format($quote->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-5 py-2 text-sm text-[#0078D4] text-right">Deposit ({{ $quote->deposit_percentage }}%)</td>
                        <td class="px-5 py-2 text-sm font-semibold text-[#0078D4] text-right">R{{ number_format($quote->depositAmount(), 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            @if ($quote->notes)
            <div class="px-5 py-4 border-t border-slate-700/50 text-sm text-slate-500">
                <p class="font-medium text-slate-300 mb-1">Notes / Terms</p>
                <p class="whitespace-pre-line">{{ $quote->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Payment plan (if accepted) --}}
        @if ($quote->paymentPlan)
        <div class="bg-slate-800 rounded-xl border border-emerald-200 p-5">
            <p class="text-sm font-semibold text-emerald-700 mb-3">Payment Plan Active</p>
            <a href="{{ route('payment-plans.show', $quote->paymentPlan) }}"
               class="text-sm text-[#0078D4] hover:text-[#002B5B] font-medium">
                View payment schedule →
            </a>
        </div>
        @endif

    </div>
</x-app-layout>
