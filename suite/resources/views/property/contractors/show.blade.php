<x-app-layout>
    <x-slot name="header">{{ $contractor->name }}</x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[#D4AF37]">{{ $contractor->name }}</h2>
                    @if($contractor->company_name)
                        <p class="text-sm text-slate-400">{{ $contractor->company_name }}</p>
                    @endif
                    <a href="{{ route('contractors.index') }}" class="text-sm text-slate-400 hover:text-white mt-0.5 inline-block">&larr; Back to Contractors</a>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if($contractor->email && !$contractor->password)
                        <form method="POST" action="{{ route('contractors.invite', $contractor) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm rounded-lg">
                                Grant Portal Access
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('contractors.edit', $contractor) }}"
                       class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
                </div>
            </div>
            @if($contractor->password && $contractor->tenant)
                @php $portalLink = route('contractor.login', $contractor->tenant->slug); @endphp
                <div class="flex items-center gap-2 bg-slate-900 rounded-lg px-3 py-2 mt-4" x-data="{ copied: false }">
                    <span class="text-xs text-slate-400 uppercase font-semibold shrink-0">Portal Link</span>
                    <span class="text-xs text-slate-300 truncate flex-1">{{ $portalLink }}</span>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $portalLink }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            class="shrink-0 text-xs font-semibold px-2 py-1 rounded-md transition-all"
                            :class="copied ? 'bg-emerald-900/40 text-emerald-400' : 'bg-slate-700 text-slate-300 hover:bg-slate-600'">
                        <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Profile Card --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Profile</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Trade</p>
                    <p class="text-slate-200 mt-0.5">{{ $contractor->trade ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Email</p>
                    <p class="text-slate-200 mt-0.5">{{ $contractor->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Phone</p>
                    <p class="text-slate-200 mt-0.5">{{ $contractor->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Portal Access</p>
                    <p class="text-slate-200 mt-0.5">{{ $contractor->password ? 'Granted' : 'Not granted' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Status</p>
                    <p class="mt-0.5">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $contractor->is_active ? 'bg-emerald-900/40 text-emerald-400' : 'bg-slate-700 text-slate-400' }}">
                            {{ $contractor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>
            @if($contractor->notes)
                <div class="mt-4 pt-4 border-t border-slate-700">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Notes</p>
                    <p class="text-slate-300 text-sm mt-1 leading-relaxed">{{ $contractor->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Quotes --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Quotes</h3>
            @if($contractor->quotes->isEmpty())
                <p class="text-sm text-slate-500">No quotes submitted yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($contractor->quotes as $quote)
                        <div class="p-4 bg-slate-900 rounded-lg">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <a href="{{ route('maintenance.show', $quote->maintenance_request_id) }}" class="text-slate-200 font-medium hover:text-[#0078D4]">
                                        {{ $quote->maintenanceRequest?->title ?? 'Maintenance Request #' . $quote->maintenance_request_id }}
                                    </a>
                                    <p class="text-xs text-slate-500 mt-0.5">R{{ number_format($quote->amount, 2) }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-xs font-medium shrink-0
                                    @if($quote->status === 'pending') bg-yellow-900/40 text-yellow-400
                                    @elseif($quote->status === 'approved') bg-[#001A3A]/40 text-[#0078D4]
                                    @elseif($quote->status === 'rejected') bg-red-900/40 text-red-400
                                    @elseif($quote->status === 'completed') bg-purple-900/40 text-purple-400
                                    @else bg-emerald-900/40 text-emerald-400 @endif">
                                    {{ ucfirst($quote->status) }}
                                </span>
                            </div>

                            @if($quote->status === 'pending')
                                <div class="flex gap-2 mt-3">
                                    <form method="POST" action="{{ route('maintenance-quotes.approve', $quote) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white text-xs rounded-lg">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('maintenance-quotes.reject', $quote) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-red-800 hover:bg-red-700 text-white text-xs rounded-lg">Reject</button>
                                    </form>
                                </div>
                            @elseif($quote->status === 'completed')
                                <form method="POST" action="{{ route('maintenance-quotes.mark-paid', $quote) }}" class="mt-3 flex gap-2">
                                    @csrf
                                    <input type="text" name="payment_reference" placeholder="Payment reference (optional)"
                                           class="flex-1 bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-xs px-2 py-1.5">
                                    <button type="submit" class="px-3 py-1.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-xs rounded-lg whitespace-nowrap">Mark Paid</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Won Jobs --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Won Jobs &mdash; Assigned To This Contractor</h3>
            @if($contractor->maintenanceRequests->isEmpty())
                <p class="text-sm text-slate-500">No jobs won yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($contractor->maintenanceRequests as $job)
                        <a href="{{ route('maintenance.show', $job) }}" class="flex items-center justify-between p-3 bg-slate-900 rounded-lg hover:bg-slate-700/50">
                            <span class="text-slate-200 text-sm">{{ $job->title }}</span>
                            <span class="text-xs text-slate-500">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Invited to Quote --}}
        @php $bidding = $contractor->invitedJobs->whereNotIn('id', $contractor->maintenanceRequests->pluck('id')); @endphp
        @if($bidding->isNotEmpty())
            <div class="bg-slate-800 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Invited to Quote &mdash; Not Yet Awarded</h3>
                <div class="space-y-2">
                    @foreach($bidding as $job)
                        <a href="{{ route('maintenance.show', $job) }}" class="flex items-center justify-between p-3 bg-slate-900 rounded-lg hover:bg-slate-700/50">
                            <span class="text-slate-200 text-sm">{{ $job->title }}</span>
                            <span class="text-xs text-slate-500">{{ $job->contractor_id ? 'Awarded to another contractor' : ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
