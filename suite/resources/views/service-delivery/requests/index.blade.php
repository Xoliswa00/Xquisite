<x-app-layout>
    @php
        $categoryLabels = ['software_solutions' => 'Software Solutions', 'business_automation' => 'Business Automation', 'data_intelligence' => 'Data & Intelligence', 'digital_solutions' => 'Digital Solutions', 'ongoing_support' => 'Ongoing Support', 'other' => 'Other'];
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Service Requests</h2>
            <a href="{{ route('request-service.show') }}" target="_blank" class="text-xs text-ink-faint hover:text-ink-muted">View public form &rarr;</a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="GET" class="flex gap-3">
            <select name="status" onchange="this.form.submit()" class="bg-panel-2 border border-line-2 text-ink text-sm rounded-lg px-3 py-2">
                <option value="">New &amp; Reviewed (default)</option>
                @foreach(['new', 'reviewed', 'converted', 'dismissed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>

        <div class="bg-panel-2 rounded-xl overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line-2 text-ink-muted text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">Submitted</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-2">
                    @forelse($requests as $req)
                        <tr class="hover:bg-line-2/40">
                            <td class="px-4 py-3 text-ink font-medium">
                                {{ $req->name }}
                                @if($req->status === 'new')
                                    <span class="ml-1.5 w-1.5 h-1.5 rounded-full bg-[#0078D4] inline-block"></span>
                                @endif
                                <p class="text-xs text-ink-faint font-normal">{{ $req->company ?? $req->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-ink-muted">{{ $categoryLabels[$req->category] ?? $req->category }}</td>
                            <td class="px-4 py-3 text-ink-faint text-xs">{{ $req->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($req->status === 'converted') bg-emerald-900/40 text-emerald-400
                                    @elseif($req->status === 'dismissed') bg-panel text-ink-faint
                                    @elseif($req->status === 'new') bg-yellow-900/40 text-yellow-400
                                    @else bg-panel-2 text-ink-muted border border-line-2 @endif">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('service-requests.show', $req) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-ink-faint">No requests to review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $requests->links() }}
    </div>
</x-app-layout>
