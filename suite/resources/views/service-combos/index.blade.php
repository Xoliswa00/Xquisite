<x-app-layout>
    <x-slot name="header">Service Combos</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-[#D4AF37]">Service Combos</h2>
                <p class="text-sm text-slate-400 mt-1">Bundle services together with a discount.</p>
            </div>
            <a href="{{ route('combos.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-medium rounded-lg transition-colors">
                + New Combo
            </a>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            @forelse($combos as $combo)
                @php
                    $status = $combo->status_label;
                    $badgeClass = match($status) {
                        'Live'      => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'Scheduled' => 'bg-sky-100 text-sky-700 border-sky-200',
                        'Expired'   => 'bg-slate-100 text-slate-500 border-slate-200',
                        default     => 'bg-red-100 text-red-700 border-red-200',
                    };
                @endphp
                <div class="px-5 py-4 border-b border-slate-800 last:border-0 hover:bg-slate-800/30">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <p class="font-medium text-white">{{ $combo->name }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $badgeClass }}">{{ $status }}</span>
                            </div>
                            <p class="text-sm text-slate-400 mt-0.5 truncate">{{ $combo->products->pluck('name')->join(', ') }}</p>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3 flex-wrap">
                            <div class="text-right shrink-0">
                                <p class="text-sm text-slate-400 line-through">R{{ number_format($combo->total_service_price, 2) }}</p>
                                <p class="font-bold text-white">R{{ number_format($combo->combo_price, 2) }}</p>
                                <p class="text-xs text-emerald-400">Save R{{ number_format($combo->savings, 2) }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <form method="POST" action="{{ route('combos.toggle', $combo) }}">
                                    @csrf
                                    <button class="text-xs px-3 py-1.5 rounded-lg border {{ $combo->is_active ? 'border-slate-700 text-slate-300 hover:bg-slate-700' : 'border-[#002B5B] text-[#0078D4] hover:bg-[#001A3A]/40' }}">
                                        {{ $combo->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('combos.edit', $combo) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-700">Edit</a>
                                <form method="POST" action="{{ route('combos.destroy', $combo) }}" onsubmit="return confirm('Delete this combo?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs px-3 py-1.5 rounded-lg border border-red-800 text-red-400 hover:bg-red-900/30">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-slate-400 text-sm">No combos yet. <a href="{{ route('combos.create') }}" class="text-[#0078D4] hover:underline">Create one.</a></div>
            @endforelse
        </div>

        {{ $combos->links() }}
    </div>
</x-app-layout>
