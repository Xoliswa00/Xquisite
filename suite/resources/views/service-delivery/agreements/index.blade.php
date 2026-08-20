<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Service Agreements</h2>
            <a href="{{ route('service-agreements.create') }}"
               class="px-4 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">+ New Agreement</a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="GET" class="flex gap-3">
            <select name="status" onchange="this.form.submit()"
                    class="bg-panel-2 border border-line-2 text-ink text-sm rounded-lg px-3 py-2">
                <option value="">All statuses</option>
                @foreach(['pending', 'active', 'suspended', 'terminated'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @if(request('status'))
                <a href="{{ route('service-agreements.index') }}" class="text-sm px-4 py-2 rounded-lg text-ink-muted hover:text-ink border border-line-2">Clear</a>
            @endif
        </form>

        <div class="bg-panel-2 rounded-xl overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line-2 text-ink-muted text-left">
                        <th class="px-4 py-3 font-medium">Client</th>
                        <th class="px-4 py-3 font-medium">Plan</th>
                        <th class="px-4 py-3 font-medium">Monthly Fee</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Late Stage</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-2">
                    @forelse($agreements as $agreement)
                        <tr class="hover:bg-line-2/40">
                            <td class="px-4 py-3 text-ink font-medium">{{ $agreement->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-muted">{{ $agreement->plan_name }}</td>
                            <td class="px-4 py-3 text-ink-muted">R{{ number_format($agreement->monthly_fee, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($agreement->status === 'active') bg-emerald-900/40 text-emerald-400
                                    @elseif($agreement->status === 'pending') bg-yellow-900/40 text-yellow-400
                                    @elseif($agreement->status === 'suspended') bg-orange-900/40 text-orange-400
                                    @else bg-red-900/40 text-red-400 @endif">
                                    {{ ucfirst($agreement->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-ink-faint text-xs">
                                {{ $agreement->late_stage === 'current' ? '—' : str_replace('_', ' ', ucfirst($agreement->late_stage)) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('service-agreements.show', $agreement) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-ink-faint">No service agreements yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $agreements->links() }}
    </div>
</x-app-layout>
