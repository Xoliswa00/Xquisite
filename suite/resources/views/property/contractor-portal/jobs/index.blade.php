@extends('property.contractor-portal.layout')

@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-slate-900">My Jobs</h1>

    @if($jobs->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
            No jobs assigned to you yet.
        </div>
    @else
        <div class="space-y-3">
            @foreach($jobs as $job)
                @php $quote = $job->quotes->first(); @endphp
                <a href="{{ route('contractor.jobs.show', [$slug, $job]) }}"
                   class="block bg-white rounded-2xl border border-slate-200 p-4 hover:border-[#0078D4]/40 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 truncate">{{ $job->title }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $job->property?->name }} &middot; Unit {{ $job->unit?->unit_number }}</p>
                            @if($quote)
                                <p class="text-xs text-slate-500 mt-1">
                                    Quote: R{{ number_format($quote->amount, 2) }} &middot;
                                    <span class="font-medium">{{ ucfirst($quote->status) }}</span>
                                </p>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0
                            @if($job->status === 'resolved' || $job->status === 'closed') bg-emerald-100 text-emerald-700
                            @elseif($job->status === 'in_progress') bg-blue-100 text-blue-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
        <div>{{ $jobs->links() }}</div>
    @endif

</div>
@endsection
