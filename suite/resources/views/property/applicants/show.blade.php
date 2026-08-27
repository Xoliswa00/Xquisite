<x-app-layout>
    <x-slot name="header">{{ $applicant->name }}</x-slot>

    <div class="max-w-4xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-[#D4AF37]">{{ $applicant->name }}</h2>
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @if($applicant->status === 'approved') bg-emerald-900/40 text-emerald-400
                        @elseif($applicant->status === 'rejected') bg-red-900/40 text-red-400
                        @elseif($applicant->status === 'converted') bg-[#001A3A]/40 text-[#0078D4]
                        @else bg-yellow-900/40 text-yellow-400 @endif">
                        {{ ucfirst($applicant->status) }}
                    </span>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('applicants.index') }}" class="text-sm text-slate-400 hover:text-white self-center">&larr; Applicants</a>
                    @if($applicant->status === 'pending')
                        <a href="{{ route('applicants.edit', $applicant) }}"
                           class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Applicant Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Email</p>
                    <p class="text-slate-200 mt-0.5">{{ $applicant->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Phone</p>
                    <p class="text-slate-200 mt-0.5">{{ $applicant->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">ID Number</p>
                    <p class="text-slate-200 mt-0.5">{{ $applicant->id_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Interested In</p>
                    <p class="text-slate-200 mt-0.5">
                        {{ $applicant->property?->name ?? '—' }}
                        @if($applicant->unit) — Unit {{ $applicant->unit->unit_number }} @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Number of Occupants</p>
                    <p class="text-slate-200 mt-0.5">{{ $applicant->number_of_occupants ?? '—' }}</p>
                </div>
                @if($applicant->notes)
                <div class="col-span-full">
                    <p class="text-xs text-slate-400 uppercase font-semibold">Notes</p>
                    <p class="text-slate-300 text-sm mt-0.5">{{ $applicant->notes }}</p>
                </div>
                @endif
            </div>

            @if($applicant->status !== 'pending')
                <div class="mt-4 pt-4 border-t border-slate-700 text-sm text-slate-400">
                    Screened {{ $applicant->screened_at?->format('d M Y H:i') }}
                    @if($applicant->status === 'rejected' && $applicant->rejection_reason)
                        — {{ $applicant->rejection_reason }}
                    @endif
                </div>
            @endif
        </div>

        {{-- Affordability Assessment --}}
        @php
            $ratio = $applicant->rentToIncomeRatio();
            $rating = $applicant->affordabilityRating();
            $disposable = $applicant->disposableIncome();
            $ratingConfig = [
                'good'    => ['label' => 'Affordable',    'bg' => 'bg-emerald-900/30', 'border' => 'border-emerald-700', 'text' => 'text-emerald-400'],
                'caution' => ['label' => 'Caution',       'bg' => 'bg-yellow-900/30',  'border' => 'border-yellow-700',  'text' => 'text-yellow-400'],
                'risk'    => ['label' => 'Affordability Risk', 'bg' => 'bg-red-900/30', 'border' => 'border-red-700',  'text' => 'text-red-400'],
            ];
        @endphp
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-300">Affordability</h3>
                @if($rating)
                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $ratingConfig[$rating]['bg'] }} {{ $ratingConfig[$rating]['text'] }} border {{ $ratingConfig[$rating]['border'] }}">
                        {{ $ratingConfig[$rating]['label'] }}
                    </span>
                @endif
            </div>

            @if(!$applicant->unit)
                <p class="text-sm text-slate-500">Link this applicant to a specific unit to assess affordability against its rent.</p>
            @elseif(!$applicant->monthly_income)
                <p class="text-sm text-slate-500">Add a monthly income to assess affordability.</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Unit Rent</p>
                        <p class="text-slate-200 mt-0.5">R{{ number_format($applicant->assessedRent(), 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Monthly Income</p>
                        <p class="text-slate-200 mt-0.5">R{{ number_format($applicant->monthly_income, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Monthly Expenses</p>
                        <p class="text-slate-200 mt-0.5">{{ $applicant->monthly_expenses ? 'R'.number_format($applicant->monthly_expenses, 2) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Rent-to-Income</p>
                        <p class="mt-0.5 font-semibold {{ $rating ? $ratingConfig[$rating]['text'] : 'text-slate-200' }}">{{ $ratio }}%</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-700 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Employer</p>
                        <p class="text-slate-200 mt-0.5">{{ $applicant->employer ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Employment</p>
                        <p class="text-slate-200 mt-0.5">
                            {{ $applicant->employment_type ? ucfirst(str_replace('_', ' ', $applicant->employment_type)) : '—' }}
                            @if($applicant->employment_months) &middot; {{ $applicant->employment_months }} month(s) @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Disposable Income</p>
                        <p class="mt-0.5 {{ $disposable !== null && $disposable < 0 ? 'text-red-400 font-semibold' : 'text-slate-200' }}">
                            {{ $disposable !== null ? 'R'.number_format($disposable, 2) . '/month after rent + expenses' : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-semibold">Previous Landlord</p>
                        <p class="text-slate-200 mt-0.5">
                            {{ $applicant->previous_landlord_name ?? '—' }}
                            @if($applicant->previous_landlord_phone) &middot; {{ $applicant->previous_landlord_phone }} @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Documents --}}
        @php
            $docLabels = [
                'id_copy' => 'ID Copy', 'proof_of_income' => 'Proof of Income',
                'bank_statement' => 'Bank Statement', 'proof_of_residence' => 'Proof of Residence', 'other' => 'Other',
            ];
            $docsByType = $applicant->documents->groupBy('type');
        @endphp
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Documents</h3>
            @if($applicant->documents->isEmpty())
                <p class="text-sm text-slate-500">No documents uploaded yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($docsByType as $type => $docs)
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-semibold mb-2">{{ $docLabels[$type] ?? ucfirst($type) }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($docs as $doc)
                                    <a href="{{ $doc->url() }}" target="_blank"
                                       class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs rounded-lg">
                                        {{ $doc->original_name ?? 'View file' }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Screening decision --}}
        @if($applicant->status === 'pending')
            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Screening Decision</h3>
                <div class="flex gap-3 flex-wrap">
                    <form method="POST" action="{{ route('applicants.screen', $applicant) }}">
                        @csrf
                        <input type="hidden" name="decision" value="approved">
                        <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm rounded-lg font-medium">
                            Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ route('applicants.screen', $applicant) }}" x-data="{ open: false }" class="flex items-start gap-3">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <div x-show="!open">
                            <button type="button" @click="open = true" class="px-4 py-2 bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm rounded-lg border border-red-800">
                                Reject
                            </button>
                        </div>
                        <div x-show="open" class="flex gap-2 items-start">
                            <input type="text" name="rejection_reason" placeholder="Reason for rejection"
                                   class="bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-600 text-white text-sm rounded-lg font-medium">
                                Confirm Reject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Convert to renter --}}
        @if($applicant->status === 'approved')
            <div class="bg-slate-800 rounded-xl p-6 space-y-3">
                <h3 class="text-sm font-semibold text-slate-300">Convert to Renter</h3>
                <p class="text-sm text-slate-400">Approved applicants stay separate from tenants until a lease is actually signed. Converting creates a renter profile you can then attach a lease to.</p>
                <form method="POST" action="{{ route('applicants.convert', $applicant) }}"
                      onsubmit="return confirm('Convert this applicant to a renter?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm rounded-lg font-medium">
                        Convert to Renter
                    </button>
                </form>
            </div>
        @endif

        @if($applicant->status === 'converted' && $applicant->renter)
            <div class="bg-slate-800 rounded-xl p-6">
                <p class="text-sm text-slate-400">
                    Converted to renter —
                    <a href="{{ route('renters.show', $applicant->renter) }}" class="text-[#0078D4] hover:text-[#B8D4F0]">{{ $applicant->renter->name }}</a>
                </p>
            </div>
        @endif

    </div>
</x-app-layout>
