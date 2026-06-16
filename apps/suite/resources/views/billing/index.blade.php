<x-app-layout>
    <x-slot name="header">Billing</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h2 class="text-xl font-bold text-white">Platform Billing</h2>
            <p class="text-sm text-slate-400 mt-1">Your subscription and payment history.</p>
        </div>

        {{-- Suspended banner --}}
        @if($tenant->suspended_at)
            <div class="bg-red-900/40 border border-red-700 rounded-2xl px-5 py-4">
                <p class="font-semibold text-red-300">Your account is suspended</p>
                <p class="text-sm text-red-400 mt-1">Please pay your outstanding invoice to restore access.</p>
            </div>
        @endif

        {{-- Grace countdown bar --}}
        @if($tenant->grace_period_ends_at && !$tenant->suspended_at)
            <div class="bg-amber-900/30 border border-amber-700 rounded-2xl px-5 py-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-semibold text-amber-300">Grace Period Active — {{ $tenant->graceDaysLeft() }} day(s) left</p>
                    <span class="text-sm text-amber-400">{{ $gracePercent }}% elapsed</span>
                </div>
                <div class="h-2 bg-amber-900/50 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full transition-all" style="width: {{ $gracePercent }}%"></div>
                </div>
                <p class="text-xs text-amber-400 mt-2">Pay your outstanding invoice to prevent suspension.</p>
            </div>
        @endif

        {{-- Plan overview --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">Current Plan</p>
                    <p class="text-xl font-bold text-white mt-1">{{ ucfirst($tenant->plan ?? 'Basic') }}</p>
                    <p class="text-sm text-slate-400 mt-1">R{{ number_format(\App\Models\Tenant::planAmount($tenant->plan ?? 'basic'), 2) }} / month</p>
                </div>
                @php $badge = $tenant->billingStatusLabel(); @endphp
                <span class="px-3 py-1.5 rounded-full text-sm font-medium border {{ $tenant->billingStatusClass() }}">{{ $badge }}</span>
            </div>
        </div>

        {{-- Outstanding invoices --}}
        @if($unpaid->count())
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-800">
                    <p class="font-semibold text-white">Outstanding Invoices</p>
                </div>
                @foreach($unpaid as $inv)
                    <div class="flex items-center px-5 py-4 border-b border-slate-800 last:border-0">
                        <div class="flex-1">
                            <p class="font-medium text-white">{{ $inv->invoice_number }}</p>
                            <p class="text-sm text-slate-400">Due {{ $inv->due_date->format('d M Y') }}</p>
                        </div>
                        <div class="text-right mr-4">
                            <p class="font-bold text-white">R{{ number_format($inv->amount, 2) }}</p>
                            @php $badge = $inv->status_badge; @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full border {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        </div>
                        <a href="{{ route('billing.show', $inv) }}" class="text-sm px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg">View</a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Invoice history --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-800">
                <p class="font-semibold text-white">Invoice History</p>
            </div>
            @forelse($invoices as $inv)
                <div class="flex items-center px-5 py-4 border-b border-slate-800 last:border-0 hover:bg-slate-800/30">
                    <div class="flex-1">
                        <a href="{{ route('billing.show', $inv) }}" class="font-medium text-white hover:text-indigo-400">{{ $inv->invoice_number }}</a>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $inv->billing_period_start->format('d M') }} – {{ $inv->billing_period_end->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white">R{{ number_format($inv->amount, 2) }}</p>
                        @php $badge = $inv->status_badge; @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full border {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">No invoices yet.</div>
            @endforelse
        </div>

        {{ $invoices->links() }}
    </div>
</x-app-layout>
