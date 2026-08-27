<x-app-layout>
    <x-slot name="header">{{ $inspection->type === 'move_in' ? 'Move-In' : 'Move-Out' }} Inspection &mdash; Lease #{{ $lease->id }}</x-slot>

    <div class="max-w-4xl mx-auto p-6 space-y-6">

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[#D4AF37]">{{ $inspection->type === 'move_in' ? 'Move-In' : 'Move-Out' }} Inspection</h2>
                    <a href="{{ route('leases.show', $lease) }}" class="text-sm text-slate-400 hover:text-white mt-0.5 inline-block">&larr; Back to Lease #{{ $lease->id }}</a>
                </div>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    {{ $inspection->status === 'completed' ? 'bg-emerald-900/40 text-emerald-400' : 'bg-yellow-900/40 text-yellow-400' }}">
                    {{ $inspection->status === 'completed' ? 'Completed' : 'In Progress' }}
                </span>
            </div>
            <p class="text-sm text-slate-400 mt-2">
                {{ $lease->property?->name }} &mdash; Unit {{ $lease->unit?->unit_number }} &middot; {{ $lease->renter?->name }}
            </p>
            @if($inspection->status === 'completed')
                <p class="text-xs text-slate-500 mt-1">Completed {{ $inspection->completed_at->format('d M Y H:i') }}</p>
            @endif
        </div>

        @if($inspection->type === 'move_out')
            @php $moveIn = $lease->inspections->firstWhere('type', 'move_in'); @endphp
            @if($moveIn)
                <div class="p-4 bg-[#0078D4]/10 border border-[#0078D4]/30 text-[#B8D4F0] rounded-xl text-sm">
                    A move-in inspection exists for this lease &mdash;
                    <a href="{{ route('leases.inspections.show', [$lease, $moveIn]) }}" class="underline hover:no-underline">compare against it</a>
                    when assessing deposit deductions.
                </div>
            @else
                <div class="p-4 bg-yellow-900/30 border border-yellow-700 text-yellow-300 rounded-xl text-sm">
                    No move-in inspection was recorded for this lease &mdash; there's nothing to compare condition against.
                </div>
            @endif
        @endif

        {{-- Checklist --}}
        <form method="POST" action="{{ route('leases.inspections.store', [$lease, $inspection]) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($inspection->sections as $section)
                    <div class="bg-slate-800 rounded-xl p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-200">{{ $section->name }}</h3>
                            @if($section->photo_path)
                                <span class="text-xs text-emerald-400">&#10003; Photo added</span>
                            @else
                                <span class="text-xs text-slate-500">No photo yet</span>
                            @endif
                        </div>

                        @if($section->photo_path)
                            <img src="{{ $section->url() }}" alt="{{ $section->name }}" class="w-full h-32 object-cover rounded-lg border border-slate-700">
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Photo {{ $section->photo_path ? '(replace)' : '*' }}</label>
                            <input type="file" name="sections[{{ $section->id }}][photo]" accept="image/png,image/jpeg,image/webp"
                                   class="w-full text-xs text-slate-300 file:mr-2 file:py-1.5 file:px-2 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 file:text-xs hover:file:bg-slate-600">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Condition</label>
                                <select name="sections[{{ $section->id }}][condition]" class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-xs px-2 py-1.5">
                                    <option value="">&mdash;</option>
                                    @foreach(['good','fair','poor','damaged'] as $c)
                                        <option value="{{ $c }}" @selected($section->condition === $c)>{{ ucfirst($c) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Notes</label>
                            <textarea name="sections[{{ $section->id }}][notes]" rows="2"
                                      class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-xs px-2 py-1.5">{{ $section->notes }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-semibold">
                    Save Progress
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
