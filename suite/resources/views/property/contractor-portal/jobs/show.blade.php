@extends('property.contractor-portal.layout')

@section('content')
<div class="space-y-6">

    <div>
        <a href="{{ route('contractor.jobs', $slug) }}" class="text-sm text-slate-400 hover:text-slate-700">&larr; My Jobs</a>
        <div class="flex items-start justify-between gap-4 mt-2">
            <h1 class="text-2xl font-bold text-slate-900">{{ $job->title }}</h1>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0 mt-1
                @if($job->status === 'resolved' || $job->status === 'closed') bg-emerald-100 text-emerald-700
                @elseif($job->status === 'in_progress') bg-blue-100 text-blue-700
                @else bg-yellow-100 text-yellow-700 @endif">
                {{ ucfirst(str_replace('_', ' ', $job->status)) }}
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-1">{{ $job->property?->name }} &middot; Unit {{ $job->unit?->unit_number }}</p>
    </div>

    @if($awardedToOther)
        <div class="p-4 bg-slate-100 border border-slate-200 text-slate-600 rounded-xl text-sm">
            This job was awarded to another contractor{{ $quote && $quote->status === 'rejected' ? ' — your quote was not selected' : '' }}.
        </div>
    @endif

    {{-- Description --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-2">Description</h2>
        <p class="text-sm text-slate-600 leading-relaxed">{{ $job->description }}</p>
        <p class="text-xs text-slate-400 mt-3">
            Priority:
            <span class="font-medium
                @if($job->priority === 'urgent') text-red-600
                @elseif($job->priority === 'high') text-orange-600
                @else text-slate-500 @endif">
                {{ ucfirst($job->priority) }}
            </span>
        </p>
    </div>

    {{-- Quote --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Quote</h2>

        @if($awardedToOther && (!$quote || $quote->status === 'rejected'))
            <p class="text-sm text-slate-400">This job is no longer open for quotes.</p>
        @elseif(!$quote || $quote->status === 'rejected')
            @if($quote && $quote->status === 'rejected')
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                    Your previous quote was rejected{{ $quote->notes ? ': ' . $quote->notes : '.' }} You may submit a new one below.
                </div>
            @endif
            <form method="POST" action="{{ route('contractor.jobs.quote', [$slug, $job]) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Quote Amount (R) *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                           class="w-full border-slate-300 rounded-xl text-sm" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full border-slate-300 rounded-xl text-sm"
                              placeholder="Scope of work, materials, timeline…"></textarea>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl text-sm">
                    Submit Quote
                </button>
            </form>
        @else
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-2xl font-bold text-slate-900">R{{ number_format($quote->amount, 2) }}</p>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        @if($quote->status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($quote->status === 'approved') bg-blue-100 text-blue-700
                        @elseif($quote->status === 'completed') bg-purple-100 text-purple-700
                        @elseif($quote->status === 'paid') bg-emerald-100 text-emerald-700
                        @else bg-slate-100 text-slate-600 @endif">
                        {{ ucfirst($quote->status) }}
                    </span>
                </div>
                @if($quote->notes)
                    <p class="text-sm text-slate-600">{{ $quote->notes }}</p>
                @endif

                @if($quote->status === 'pending')
                    <p class="text-xs text-slate-400">Awaiting review from the property manager.</p>
                @elseif($quote->status === 'approved')
                    <p class="text-xs text-slate-400">Approved &mdash; you can proceed with the work.</p>
                    <form method="POST" action="{{ route('contractor.jobs.complete', [$slug, $job]) }}" class="pt-2">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm">
                            Mark Job Complete
                        </button>
                    </form>
                @elseif($quote->status === 'completed')
                    <p class="text-xs text-slate-400">Marked complete on {{ $quote->completed_at?->format('d M Y') }} &mdash; awaiting payment.</p>
                @elseif($quote->status === 'paid')
                    <p class="text-xs text-emerald-600">Paid on {{ $quote->paid_at?->format('d M Y') }}{{ $quote->payment_reference ? ' — ref: ' . $quote->payment_reference : '' }}.</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Photos --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <h2 class="text-sm font-semibold text-slate-700">Photos</h2>

        @if($job->photos->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($job->photos as $photo)
                    <img src="{{ $photo->url() }}" alt="{{ $job->title }}"
                         class="w-full h-28 object-cover rounded-xl border border-slate-200">
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400">No photos yet.</p>
        @endif

        <form method="POST" action="{{ route('contractor.jobs.photos', [$slug, $job]) }}"
              enctype="multipart/form-data" class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-3">
            @csrf
            <input type="file" name="photos[]" multiple accept="image/png,image/jpeg,image/webp" required
                   class="text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:text-sm hover:file:bg-slate-200">
            <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-lg">
                Add Photos
            </button>
        </form>
    </div>

</div>
@endsection
