<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-2xl font-bold text-white">{{ $maintenance->title }}</h2>
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @if($maintenance->priority === 'urgent') bg-red-900/40 text-red-400
                        @elseif($maintenance->priority === 'high') bg-orange-900/40 text-orange-400
                        @elseif($maintenance->priority === 'medium') bg-yellow-900/40 text-yellow-400
                        @else bg-slate-700 text-slate-400 @endif">
                        {{ ucfirst($maintenance->priority) }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @if($maintenance->status === 'open') bg-yellow-900/40 text-yellow-400
                        @elseif($maintenance->status === 'in_progress') bg-indigo-900/40 text-indigo-400
                        @elseif($maintenance->status === 'resolved') bg-emerald-900/40 text-emerald-400
                        @else bg-slate-700 text-slate-400 @endif">
                        {{ ucfirst(str_replace('_', ' ', $maintenance->status)) }}
                    </span>
                </div>
                <a href="{{ route('maintenance.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Maintenance</a>
            </div>
            <a href="{{ route('maintenance.edit', $maintenance) }}"
               class="px-3 py-2 bg-indigo-700 hover:bg-indigo-600 text-white text-sm rounded-lg">Edit</a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Info --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Request Information</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Property</p>
                    <p class="text-slate-200 mt-0.5">{{ $maintenance->property?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Unit</p>
                    <p class="text-slate-200 mt-0.5">{{ $maintenance->unit?->unit_number ?? '—' }}</p>
                </div>
                @if($maintenance->renter)
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Renter</p>
                    <p class="text-slate-200 mt-0.5">
                        <a href="{{ route('renters.show', $maintenance->renter) }}" class="hover:text-indigo-400">{{ $maintenance->renter->name }}</a>
                    </p>
                </div>
                @endif
                @if($maintenance->lease)
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Lease</p>
                    <p class="text-slate-200 mt-0.5">
                        <a href="{{ route('leases.show', $maintenance->lease) }}" class="hover:text-indigo-400">Lease #{{ $maintenance->lease->id }}</a>
                    </p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Reported At</p>
                    <p class="text-slate-200 mt-0.5">{{ $maintenance->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Assigned To</p>
                    <p class="text-slate-200 mt-0.5">{{ $maintenance->assigned_to ?? '—' }}</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-700">
                <p class="text-xs text-slate-400 uppercase font-semibold">Description</p>
                <p class="text-slate-300 text-sm mt-1 leading-relaxed">{{ $maintenance->description }}</p>
            </div>
        </div>

        {{-- Resolution Notes (if resolved) --}}
        @if($maintenance->status === 'resolved' && $maintenance->resolution_notes)
            <div class="bg-emerald-900/20 border border-emerald-800 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-emerald-400 mb-2">Resolution</h3>
                @if($maintenance->resolved_at)
                    <p class="text-xs text-slate-400 mb-2">Resolved on {{ \Carbon\Carbon::parse($maintenance->resolved_at)->format('d M Y H:i') }}</p>
                @endif
                <p class="text-slate-300 text-sm leading-relaxed">{{ $maintenance->resolution_notes }}</p>
            </div>
        @endif

        {{-- Status Update Form --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Update Status</h3>
            <form method="POST" action="{{ route('maintenance.status', $maintenance) }}" class="space-y-4">
                @csrf

                @if($errors->any())
                    <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                        <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            @foreach(['open','in_progress','resolved','closed'] as $s)
                                <option value="{{ $s }}" @selected($maintenance->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Assigned To</label>
                        <input type="text" name="assigned_to" value="{{ old('assigned_to', $maintenance->assigned_to) }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2"
                               placeholder="Name or team">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Resolution Notes</label>
                    <textarea name="resolution_notes" rows="3"
                              class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2"
                              placeholder="Describe what was done to resolve the issue...">{{ old('resolution_notes', $maintenance->resolution_notes) }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-semibold">
                        Update Status
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
