@php
    $totalValue = $promoCode->redemptions->sum('financial_value');
@endphp

<x-app-layout>
    <x-slot name="header">Promo Code</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-mono">{{ $promoCode->code }}</h2>
                <p class="text-slate-400 text-sm mt-1">{{ $promoCode->describeDiscount() }} @if($promoCode->source) &middot; {{ $promoCode->source }} @endif</p>
            </div>
            <a href="{{ route('admin.promo-codes.index') }}" class="text-sm text-slate-400 hover:text-slate-200">&larr; Back to list</a>
        </div>

        @if($promoCode->isExhausted())
            @php
                $statusText = match(true) {
                    !$promoCode->is_active => 'deactivated',
                    $promoCode->isExpired() => 'expired',
                    default => 'still active',
                };
            @endphp
            <div class="p-4 bg-amber-900/20 border border-amber-700/50 text-amber-400 rounded-xl text-sm flex items-start gap-2">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>
                    This code has been redeemed {{ $promoCode->times_redeemed }} times, past its cap of {{ $promoCode->max_redemptions }} — it's {{ $statusText }}, so redemptions can still be recorded below. This is a soft cap for visibility, not a hard stop; review whether that's intended.
                </span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Status</p>
                <p class="text-lg font-bold mt-1 {{ $promoCode->isRedeemable() ? 'text-emerald-400' : 'text-slate-400' }}">
                    {{ $promoCode->isRedeemable() ? 'Active' : ($promoCode->is_active ? 'Expired' : 'Deactivated') }}
                </p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Redemptions</p>
                <p class="text-lg font-bold mt-1 {{ $promoCode->isExhausted() ? 'text-amber-400' : 'text-white' }}">
                    {{ $promoCode->times_redeemed }}{{ $promoCode->max_redemptions ? ' / ' . $promoCode->max_redemptions : '' }}
                </p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Value given away</p>
                <p class="text-lg font-bold text-[#D4AF37] mt-1">R{{ number_format($totalValue, 2) }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Expires</p>
                <p class="text-lg font-bold text-white mt-1">{{ $promoCode->expires_at?->format('d M Y') ?? 'Never' }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-300">Redemption history</h3>
                </div>
                @if($promoCode->redemptions->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm summary-on-mobile">
                            <thead class="bg-slate-900/50 border-b border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-300">Against</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-300">Value</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-300">Redeemed by</th>
                                    <th class="px-6 py-3 text-left font-semibold text-slate-300">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                @foreach($promoCode->redemptions as $r)
                                    <tr>
                                        <td class="px-6 py-4 text-slate-300">
                                            {{ $r->tenant?->name ?? $r->foundingTwentyApplication?->business_name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-[#D4AF37]">R{{ number_format($r->financial_value, 2) }}</td>
                                        <td class="px-6 py-4 text-slate-300">{{ $r->redeemer?->name ?? '—' }}</td>
                                        <td class="px-6 py-4 text-xs text-slate-400">{{ $r->redeemed_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-6 py-8 text-sm text-slate-400 text-center">No redemptions recorded yet.</p>
                @endif
            </div>

            <div class="space-y-6">
                @if($promoCode->isRedeemable())
                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-3">
                        <h3 class="text-sm font-semibold text-slate-300">Record a redemption</h3>
                        <form method="POST" action="{{ route('admin.promo-codes.redeem', $promoCode) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Tenant (if one exists)</label>
                                <select name="tenant_id" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                                    <option value="">—</option>
                                    @foreach($tenants as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Founding 20 application (if applicable)</label>
                                <select name="founding_twenty_application_id" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                                    <option value="">—</option>
                                    @foreach($foundingTwentyApplications as $app)
                                        <option value="{{ $app->id }}">{{ $app->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Financial value (R) *</label>
                                <input type="number" step="0.01" min="0" name="financial_value" required class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Notes</label>
                                <textarea name="notes" rows="2" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg py-2 text-sm font-medium transition">
                                Record redemption
                            </button>
                        </form>
                    </div>
                @endif

                @if($promoCode->is_active)
                    <form method="POST" action="{{ route('admin.promo-codes.deactivate', $promoCode) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Deactivate this code? It will no longer be redeemable.')"
                                class="w-full px-3 py-2 text-sm bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition">
                            Deactivate code
                        </button>
                    </form>
                @endif

                @if($promoCode->notes)
                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                        <h3 class="text-sm font-semibold text-slate-300 mb-2">Notes</h3>
                        <p class="text-sm text-slate-400">{{ $promoCode->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
