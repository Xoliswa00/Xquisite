@extends('property.portal.layout')

@section('content')
<div class="space-y-8">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Maintenance</h1>
    </div>

    {{-- Submit new request --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4">Report an Issue</h2>
        <form method="POST" action="{{ route('rent.maintenance.submit', $slug) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border-slate-300 rounded-xl text-sm" placeholder="e.g. Leaking tap in bathroom">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description *</label>
                <textarea name="description" rows="3" required
                          class="w-full border-slate-300 rounded-xl text-sm"
                          placeholder="Describe the issue in detail…">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Priority *</label>
                    <select name="priority" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="low">Low — not urgent</option>
                        <option value="medium" selected>Medium — needs attention</option>
                        <option value="high">High — affecting daily use</option>
                        <option value="urgent">Urgent — emergency</option>
                    </select>
                </div>
            </div>
            <button type="submit"
                    class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0078D4] text-white font-semibold rounded-xl transition text-sm">
                Submit Request
            </button>
        </form>
    </div>

    {{-- Existing requests --}}
    @if($requests->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-slate-800 mb-3">Your Requests</h2>
        <div class="space-y-3">
            @foreach($requests as $req)
            <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                            @if($req->priority === 'urgent') bg-red-100 text-red-700
                            @elseif($req->priority === 'high') bg-orange-100 text-orange-700
                            @elseif($req->priority === 'medium') bg-yellow-100 text-yellow-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ ucfirst($req->priority) }}
                        </span>
                        <p class="font-semibold text-slate-900">{{ $req->title }}</p>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Reported {{ $req->created_at->diffForHumans() }}</p>
                    @if($req->assigned_to)
                        <p class="text-xs text-slate-400">Assigned to: {{ $req->assigned_to }}</p>
                    @endif
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0
                    @if($req->status === 'resolved' || $req->status === 'closed') bg-emerald-100 text-emerald-700
                    @elseif($req->status === 'in_progress') bg-blue-100 text-blue-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                </span>
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $requests->links() }}</div>
    </div>
    @endif

</div>
@endsection
