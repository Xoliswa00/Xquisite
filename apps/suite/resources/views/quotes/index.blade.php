<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Quotes</h2>
            <a href="{{ route('quotes.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                + New Quote
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100 summary-on-mobile">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Reference</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Title</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Client</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Valid Until</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($quotes as $quote)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4 font-mono text-sm text-gray-500">{{ $quote->reference }}</td>
                        <td class="px-5 py-4 font-medium text-gray-900 text-sm">{{ $quote->title }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">
                            {{ $quote->customer?->name ?? $quote->client_email ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900 text-right">R{{ number_format($quote->total, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-gray-500">
                            @if ($quote->valid_until)
                                <span class="{{ $quote->isExpired() ? 'text-red-500' : '' }}">
                                    {{ $quote->valid_until->format('d M Y') }}
                                </span>
                            @else —
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $colours = [
                                    'draft'     => 'bg-gray-100 text-gray-600',
                                    'sent'      => 'bg-blue-100 text-blue-700',
                                    'accepted'  => 'bg-emerald-100 text-emerald-700',
                                    'declined'  => 'bg-red-100 text-red-700',
                                    'expired'   => 'bg-amber-100 text-amber-700',
                                    'converted' => 'bg-indigo-100 text-indigo-700',
                                ];
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $colours[$quote->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($quote->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('quotes.show', $quote) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <p>No quotes yet.</p>
                            <p class="text-sm mt-1">Create a quote for a catering package, decor proposal, or custom service.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($quotes->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $quotes->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>
