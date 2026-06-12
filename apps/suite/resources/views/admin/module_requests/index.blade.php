<x-app-layout>
    <x-slot name="header">Module Requests</x-slot>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-semibold text-white">Pending module requests</h2>
                <p class="text-sm text-slate-400">Review requests and approve or reject activation/modification work.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700 text-sm text-left summary-on-mobile">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3">Tenant</th>
                        <th class="px-4 py-3">Module</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Requested by</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Requested</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($requests as $request)
                        <tr class="bg-slate-900/50">
                            <td class="px-4 py-4 font-medium text-white">{{ $request->tenant->name }}</td>
                            <td class="px-4 py-4 text-slate-200">{{ $request->module_name }}</td>
                            <td class="px-4 py-4 text-slate-400">{{ $request->readable_type }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ $request->user->name }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $request->status === 'pending' ? 'bg-amber-500/15 text-amber-300' : ($request->status === 'approved' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/15 text-red-300') }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-400">{{ $request->requested_at->diffForHumans() }}</td>
                            <td class="px-4 py-4 space-y-2">
                                @if($request->status === 'pending')
                                    <form action="{{ route('admin.module-requests.approve', $request) }}" method="POST" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="price_override" step="0.01" placeholder="Price override"
                                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ old('price_override', $request->price_override) }}">
                                        <textarea name="review_notes" rows="2" placeholder="Review notes (optional)"
                                                  class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                                        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-900 rounded-xl px-3 py-2 text-xs font-semibold transition-colors">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.module-requests.reject', $request) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full bg-red-500 hover:bg-red-400 text-white rounded-xl px-3 py-2 text-xs font-semibold transition-colors">Reject</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Reviewed {{ $request->reviewed_at ? $request->reviewed_at->diffForHumans() : '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No module requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
