<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-[#D4AF37]">Module Requests</h2>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-slate-800 rounded-2xl border border-slate-700">

        <div class="px-5 py-4 border-b border-slate-700">
            <p class="text-sm text-slate-400">Review requests and approve or reject activation/modification work.</p>
        </div>

        {{-- Mobile cards (hidden on sm+) --}}
        <div class="sm:hidden divide-y divide-slate-700">
            @forelse($requests as $request)
                <div class="p-4 space-y-3">
                    {{-- Header row: tenant + status badge --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ $request->tenant->name }}</p>
                            <p class="text-xs text-[#B8D4F0] mt-0.5">{{ $request->module_name }}</p>
                        </div>
                        <span class="shrink-0 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                            {{ $request->status === 'pending'  ? 'bg-amber-500/15 text-amber-300'  :
                               ($request->status === 'approved' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/15 text-red-300') }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>

                    {{-- Meta --}}
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-400">
                        <span class="text-slate-500">Type</span>
                        <span class="text-slate-300">{{ $request->readable_type }}</span>
                        <span class="text-slate-500">Requested by</span>
                        <span class="text-slate-300">{{ $request->user->name }}</span>
                        <span class="text-slate-500">When</span>
                        <span class="text-slate-300">{{ $request->requested_at->diffForHumans() }}</span>
                    </div>

                    {{-- Actions --}}
                    @if($request->status === 'pending')
                        <form action="{{ route('admin.module-requests.approve', $request) }}" method="POST" class="space-y-2">
                            @csrf @method('PATCH')
                            <input type="number" name="price_override" step="0.01" placeholder="Price override (optional)"
                                   value="{{ old('price_override', $request->price_override) }}"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <textarea name="review_notes" rows="2" placeholder="Review notes (optional)"
                                      class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"></textarea>
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-slate-900 rounded-lg px-3 py-2 text-xs font-semibold transition-colors">
                                    Approve
                                </button>
                                <a href="#" onclick="event.preventDefault();this.closest('div').nextElementSibling.querySelector('button').click()"
                                   class="sr-only">reject helper</a>
                            </div>
                        </form>
                        <form action="{{ route('admin.module-requests.reject', $request) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="w-full bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg px-3 py-2 text-xs font-semibold transition-colors border border-red-500/30">
                                Reject
                            </button>
                        </form>
                    @else
                        <p class="text-xs text-slate-500">
                            Reviewed {{ $request->reviewed_at ? $request->reviewed_at->diffForHumans() : '—' }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="px-4 py-10 text-center text-slate-500 text-sm">No module requests found.</div>
            @endforelse
        </div>

        {{-- Desktop table (hidden on mobile) --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700 text-sm text-left">
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
                        <tr class="bg-slate-900/50 hover:bg-slate-900/80 transition-colors">
                            <td class="px-4 py-4 font-medium text-white">{{ $request->tenant->name }}</td>
                            <td class="px-4 py-4 text-slate-200">{{ $request->module_name }}</td>
                            <td class="px-4 py-4 text-slate-400">{{ $request->readable_type }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ $request->user->name }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $request->status === 'pending'  ? 'bg-amber-500/15 text-amber-300'  :
                                       ($request->status === 'approved' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/15 text-red-300') }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-400 whitespace-nowrap">{{ $request->requested_at->diffForHumans() }}</td>
                            <td class="px-4 py-4">
                                @if($request->status === 'pending')
                                    <div class="space-y-2 min-w-[180px]">
                                        <form action="{{ route('admin.module-requests.approve', $request) }}" method="POST" class="space-y-1.5">
                                            @csrf @method('PATCH')
                                            <input type="number" name="price_override" step="0.01" placeholder="Price override"
                                                   value="{{ old('price_override', $request->price_override) }}"
                                                   class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                            <textarea name="review_notes" rows="2" placeholder="Review notes (optional)"
                                                      class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"></textarea>
                                            <button type="submit"
                                                    class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-900 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.module-requests.reject', $request) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="w-full bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors border border-red-500/30">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Reviewed {{ $request->reviewed_at ? $request->reviewed_at->diffForHumans() : '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No module requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-5 py-4 border-t border-slate-700">
                {{ $requests->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
