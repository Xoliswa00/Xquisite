<x-app-layout>
    <x-slot name="header">Promotions</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-[#D4AF37]">Promotions</h2>
                <p class="text-sm text-slate-400 mt-1">Discount codes for your clients.</p>
            </div>
            <a href="{{ route('promotions.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-medium rounded-lg transition-colors">
                + New Promotion
            </a>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            @forelse($promotions as $promo)
                @php
                    $status = $promo->status_label;
                    $badgeClass = match($status) {
                        'Live'      => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'Scheduled' => 'bg-sky-100 text-sky-700 border-sky-200',
                        'Expired'   => 'bg-slate-100 text-slate-500 border-slate-200',
                        'Exhausted' => 'bg-orange-100 text-orange-700 border-orange-200',
                        default     => 'bg-red-100 text-red-700 border-red-200',
                    };
                @endphp
                <div class="px-5 py-4 border-b border-slate-800 last:border-0 hover:bg-slate-800/30">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <p class="font-medium text-white">{{ $promo->name }}</p>
                                <code class="text-xs bg-slate-800 px-2 py-0.5 rounded text-amber-300">{{ $promo->code }}</code>
                                <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $badgeClass }}">{{ $status }}</span>
                            </div>
                            <p class="text-sm text-slate-400 mt-0.5">
                                {{ $promo->discount_type === 'percentage' ? $promo->discount_value . '%' : 'R' . number_format($promo->discount_value, 2) }} off
                                · applies to {{ $promo->applies_to }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3 flex-wrap">
                            <div class="shrink-0">
                                @if($promo->max_uses)
                                    @php $pct = min(100, round(($promo->used_count / $promo->max_uses) * 100)); @endphp
                                    <div class="w-28">
                                        <div class="flex justify-between text-xs text-slate-400 mb-1">
                                            <span>{{ $promo->used_count }}/{{ $promo->max_uses }}</span>
                                            <span>{{ $pct }}%</span>
                                        </div>
                                        <div class="h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-[#0078D4] rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-xs text-slate-400">{{ $promo->used_count }} uses</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                <form method="POST" action="{{ route('promotions.toggle', $promo) }}">
                                    @csrf
                                    <button class="text-xs px-3 py-1.5 rounded-lg border {{ $promo->is_active ? 'border-slate-700 text-slate-300 hover:bg-slate-700' : 'border-[#002B5B] text-[#0078D4] hover:bg-[#001A3A]/40' }}">
                                        {{ $promo->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('promotions.edit', $promo) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-700">Edit</a>
                                <form method="POST" action="{{ route('promotions.destroy', $promo) }}" onsubmit="return confirm('Delete this promotion?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs px-3 py-1.5 rounded-lg border border-red-800 text-red-400 hover:bg-red-900/30">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-slate-400 text-sm">No promotions yet. <a href="{{ route('promotions.create') }}" class="text-[#0078D4] hover:underline">Create one.</a></div>
            @endforelse
        </div>

        {{ $promotions->links() }}
    </div>
</x-app-layout>
