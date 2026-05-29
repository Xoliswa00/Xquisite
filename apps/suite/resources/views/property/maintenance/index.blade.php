<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Maintenance Requests</h2>
            <a href="{{ route('maintenance.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg">
                + New Request
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('maintenance.index') }}" class="bg-slate-800 rounded-xl p-4">
            <div class="flex gap-3 flex-wrap">
                <select name="status" class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    <option value="">All Statuses</option>
                    @foreach(['open','in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="priority" class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    <option value="">All Priorities</option>
                    @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
                <select name="property_id" class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    <option value="">All Properties</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" @selected(request('property_id') == $property->id)>{{ $property->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg">Filter</button>
                @if(request()->hasAny(['status','priority','property_id']))
                    <a href="{{ route('maintenance.index') }}"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Priority</th>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Property / Unit</th>
                        <th class="px-4 py-3 font-medium">Renter</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                        <th class="px-4 py-3 font-medium">Assigned To</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($requests as $request)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($request->priority === 'urgent') bg-red-900/40 text-red-400
                                    @elseif($request->priority === 'high') bg-orange-900/40 text-orange-400
                                    @elseif($request->priority === 'medium') bg-yellow-900/40 text-yellow-400
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ ucfirst($request->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-200 font-medium max-w-xs">
                                <span class="block truncate">{{ $request->title }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-200">{{ $request->property?->name ?? '—' }}</p>
                                <p class="text-slate-500 text-xs">Unit {{ $request->unit?->unit_number ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $request->renter?->name ?? 'Admin' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($request->status === 'open') bg-yellow-900/40 text-yellow-400
                                    @elseif($request->status === 'in_progress') bg-indigo-900/40 text-indigo-400
                                    @elseif($request->status === 'resolved') bg-emerald-900/40 text-emerald-400
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $request->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $request->assigned_to ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('maintenance.show', $request) }}"
                                   class="text-indigo-400 hover:text-indigo-300 text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                No maintenance requests found.
                                <a href="{{ route('maintenance.create') }}" class="text-indigo-400 hover:text-indigo-300 ml-1">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $requests->withQueryString()->links() }}

    </div>
</x-app-layout>
