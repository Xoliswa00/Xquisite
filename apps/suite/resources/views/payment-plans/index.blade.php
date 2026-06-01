<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Laybys & Payment Plans</h2>
            <div class="flex items-center gap-3">
                @if ($overdueCount > 0)
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        {{ $overdueCount }} overdue
                    </span>
                @endif
                <a href="{{ route('payment-plans.create') }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                    + New Plan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Plan</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Customer</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Total</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Paid</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Progress</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Next Due</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($plans as $plan)
                    @php $next = $plan->nextDue(); @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-4">
                            <p class="font-medium text-gray-900 text-sm">{{ $plan->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst(str_replace('_', ' ', $plan->type)) }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">{{ $plan->customer?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-gray-900 text-right font-medium">R{{ number_format($plan->total_amount, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-emerald-600 text-right font-medium">R{{ number_format($plan->amount_paid, 2) }}</td>
                        <td class="px-5 py-4 w-32">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $plan->progressPercent() }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 shrink-0">{{ $plan->progressPercent() }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $plan->status === 'active'    ? 'bg-indigo-100 text-indigo-700' : '' }}
                                {{ $plan->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $plan->status === 'cancelled' ? 'bg-gray-100 text-gray-600' : '' }}
                                {{ $plan->status === 'defaulted' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            @if ($next)
                                <span class="{{ $next->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                    R{{ number_format($next->amount, 2) }}
                                    <span class="text-xs text-gray-400 ml-1">{{ $next->due_date->format('d M') }}</span>
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('payment-plans.show', $plan) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                            <p>No payment plans yet.</p>
                            <p class="text-sm mt-1">Create a layby from the POS terminal or attach a payment schedule to a booking.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($plans->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $plans->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>
