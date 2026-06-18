<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Quotes</h2>
            <a href="{{ route('quotes.create') }}"
               class="px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm rounded-lg font-medium whitespace-nowrap">
                + New Quote
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-900/50 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden overflow-x-auto">

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700/50">
                @forelse ($quotes as $quote)
                    @php
                        $colours = ['draft'=>'bg-slate-800/50 text-slate-400','sent'=>'bg-blue-100 text-blue-700','accepted'=>'bg-emerald-100 text-emerald-700','declined'=>'bg-red-100 text-red-700','expired'=>'bg-amber-100 text-amber-700','converted'=>'bg-[#E8F2FA] text-[#002B5B]'];
                    @endphp
                    <a href="{{ route('quotes.show', $quote) }}" class="block px-4 py-3 hover:bg-slate-900 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-white truncate">{{ $quote->title }}</p>
                            <span class="shrink-0 text-xs px-2 py-0.5 rounded-full font-medium {{ $colours[$quote->status] ?? 'bg-slate-800/50 text-slate-400' }}">{{ ucfirst($quote->status) }}</span>
                        </div>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="text-xs text-slate-500 font-mono">{{ $quote->reference }}</p>
                            <p class="text-xs text-slate-500">{{ $quote->customer?->name ?? $quote->client_email ?? '—' }}</p>
                            <p class="text-xs font-medium text-slate-300 ml-auto">R{{ number_format($quote->total, 2) }}</p>
                        </div>
                        @if($quote->valid_until)
                            <p class="text-xs {{ $quote->isExpired() ? 'text-red-500' : 'text-slate-500' }} mt-0.5">Until {{ $quote->valid_until->format('d M Y') }}</p>
                        @endif
                    </a>
                @empty
                    <div class="px-5 py-12 text-center text-slate-500">
                        <p>No quotes yet.</p>
                        <p class="text-sm mt-1">Create a quote for a catering package, decor proposal, or custom service.</p>
                    </div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table min-w-full divide-y divide-slate-700/50">
                <thead class="bg-slate-900">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Reference</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Title</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Client</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wide">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Valid Until</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse ($quotes as $quote)
                    @php
                        $colours = ['draft'=>'bg-slate-800/50 text-slate-400','sent'=>'bg-blue-100 text-blue-700','accepted'=>'bg-emerald-100 text-emerald-700','declined'=>'bg-red-100 text-red-700','expired'=>'bg-amber-100 text-amber-700','converted'=>'bg-[#E8F2FA] text-[#002B5B]'];
                    @endphp
                    <tr class="hover:bg-slate-900 transition">
                        <td class="px-5 py-4 font-mono text-sm text-slate-500">{{ $quote->reference }}</td>
                        <td class="px-5 py-4 font-medium text-white text-sm">{{ $quote->title }}</td>
                        <td class="px-5 py-4 text-sm text-slate-400">{{ $quote->customer?->name ?? $quote->client_email ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm font-medium text-white text-right">R{{ number_format($quote->total, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">
                            @if ($quote->valid_until)
                                <span class="{{ $quote->isExpired() ? 'text-red-500' : '' }}">{{ $quote->valid_until->format('d M Y') }}</span>
                            @else —
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $colours[$quote->status] ?? 'bg-slate-800/50 text-slate-400' }}">
                                {{ ucfirst($quote->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('quotes.show', $quote) }}" class="text-xs text-[#0078D4] hover:text-[#002B5B] font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                            <p>No quotes yet.</p>
                            <p class="text-sm mt-1">Create a quote for a catering package, decor proposal, or custom service.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($quotes->hasPages())
                <div class="px-5 py-4 border-t border-slate-700/50">{{ $quotes->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>
