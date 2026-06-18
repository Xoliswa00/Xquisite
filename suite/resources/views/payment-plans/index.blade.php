<x-app-layout>
    <x-slot name="header">Laybys & Payment Plans</x-slot>

    <div class="max-w-5xl space-y-5">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-bold text-[#D4AF37]">Laybys & Payment Plans</h1>
                @if ($overdueCount > 0)
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/15 text-red-400">
                        {{ $overdueCount }} overdue
                    </span>
                @endif
            </div>
            <a href="{{ route('payment-plans.create') }}"
               class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm rounded-lg font-medium transition-colors">
                + New Plan
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-900/50 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-700/50 summary-on-mobile">
                <thead class="bg-slate-900">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Plan</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Customer</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wide">Total</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wide">Paid</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Progress</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wide">Next Due</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse ($plans as $plan)
                    @php $next = $plan->nextDue(); @endphp
                    <tr class="hover:bg-slate-900 transition">
                        <td class="px-5 py-4">
                            <p class="font-medium text-white text-sm">{{ $plan->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ ucfirst(str_replace('_', ' ', $plan->type)) }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-400">{{ $plan->customer?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-white text-right font-medium">R{{ number_format($plan->total_amount, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-emerald-600 text-right font-medium">R{{ number_format($plan->amount_paid, 2) }}</td>
                        <td class="px-5 py-4 w-32">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-700 rounded-full h-1.5">
                                    <div class="bg-[#0078D4] h-1.5 rounded-full" style="width: {{ $plan->progressPercent() }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500 shrink-0">{{ $plan->progressPercent() }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $plan->status === 'active'    ? 'bg-[#E8F2FA] text-[#002B5B]' : '' }}
                                {{ $plan->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $plan->status === 'cancelled' ? 'bg-slate-800/50 text-slate-400' : '' }}
                                {{ $plan->status === 'defaulted' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            @if ($next)
                                <span class="{{ $next->isOverdue() ? 'text-red-600 font-medium' : 'text-slate-400' }}">
                                    R{{ number_format($next->amount, 2) }}
                                    <span class="text-xs text-slate-500 ml-1">{{ $next->due_date->format('d M') }}</span>
                                </span>
                            @else
                                <span class="text-slate-500 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('payment-plans.show', $plan) }}" class="text-xs text-[#0078D4] hover:text-[#002B5B] font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                            <p>No payment plans yet.</p>
                            <p class="text-sm mt-1">Create a layby from the POS terminal or attach a payment schedule to a booking.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($plans->hasPages())
                <div class="px-5 py-4 border-t border-slate-700/50">{{ $plans->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>
