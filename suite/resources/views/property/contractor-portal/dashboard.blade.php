@extends('property.contractor-portal.layout')

@section('content')
<div class="space-y-8">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Welcome, {{ $contractor->name }}</h1>
        <p class="text-slate-500 text-sm mt-1">Here&rsquo;s what&rsquo;s on your plate right now.</p>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
            <p class="text-2xl font-bold text-slate-900">{{ $openJobs }}</p>
            <p class="text-xs text-slate-500 mt-1">Open Jobs</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $pendingQuotes }}</p>
            <p class="text-xs text-slate-500 mt-1">Quotes Awaiting Review</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
            <p class="text-2xl font-bold text-[#0078D4]">{{ $awaitingPayment }}</p>
            <p class="text-xs text-slate-500 mt-1">Awaiting Payment</p>
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-slate-800">Assigned Jobs</h2>
            <a href="{{ route('contractor.jobs', $slug) }}" class="text-sm text-[#0078D4] hover:text-[#0065B8]">View all &rarr;</a>
        </div>

        @if($jobs->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
                No jobs assigned to you yet.
            </div>
        @else
            <div class="space-y-3">
                @foreach($jobs->take(5) as $job)
                    <a href="{{ route('contractor.jobs.show', [$slug, $job]) }}"
                       class="block bg-white rounded-2xl border border-slate-200 p-4 hover:border-[#0078D4]/40 transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $job->title }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $job->property?->name }} &middot; Unit {{ $job->unit?->unit_number }}</p>
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
        @endif
    </div>

</div>
@endsection
