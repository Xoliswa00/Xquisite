<x-app-layout>
    <x-slot name="header">Promo Codes</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Promo Codes</h2>
                <p class="text-slate-400 text-sm mt-1">Track discounts issued and their rand value given away</p>
            </div>
            <a href="{{ route('admin.promo-codes.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Code
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Total codes</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_codes'] }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Active codes</p>
                <p class="text-2xl font-bold text-[#0078D4] mt-1">{{ $stats['active_codes'] }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Total redemptions</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_redemptions'] }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Total value given away</p>
                <p class="text-2xl font-bold text-[#D4AF37] mt-1">R{{ number_format($stats['total_value_given'] ?? 0, 2) }}</p>
            </div>
        </div>

        @if($codes->count() > 0)
            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Code</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Discount</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Redemptions</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Value given</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Status</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($codes as $code)
                                @php
                                    $statusLabel = match(true) {
                                        !$code->is_active => ['label' => 'Deactivated', 'color' => 'bg-slate-700 text-slate-300'],
                                        $code->isExpired() => ['label' => 'Expired', 'color' => 'bg-red-500/20 text-red-400'],
                                        default => ['label' => 'Active', 'color' => 'bg-emerald-500/20 text-emerald-400'],
                                    };
                                @endphp
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-mono font-medium text-white">{{ $code->code }}</p>
                                        @if($code->source)
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $code->source }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-300">{{ $code->describeDiscount() }}</td>
                                    <td class="px-6 py-4">
                                        <span class="{{ $code->isExhausted() ? 'text-amber-400 font-semibold' : 'text-slate-300' }}">
                                            {{ $code->redemptions_count }}{{ $code->max_redemptions ? ' / ' . $code->max_redemptions : '' }}
                                        </span>
                                        @if($code->isExhausted())
                                            <span class="block text-xs text-amber-400 mt-0.5">Over cap</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono text-[#D4AF37]">R{{ number_format($code->redemptions_sum_financial_value ?? 0, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $statusLabel['color'] }}">{{ $statusLabel['label'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.promo-codes.show', $code) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-slate-800 rounded-xl p-12 border border-slate-700 text-center">
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <h3 class="text-lg font-semibold text-slate-300">No promo codes yet</h3>
                <p class="text-slate-400 text-sm mt-1 mb-4">Create one to start tracking discounts and what they're worth.</p>
                <a href="{{ route('admin.promo-codes.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg font-medium transition">
                    Create your first code
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
