<x-app-layout>
    <x-slot name="header">{{ $maintenance->title }}</x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        {{-- Identity --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-xl font-bold text-[#D4AF37]">{{ $maintenance->title }}</h2>
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($maintenance->priority === 'urgent') bg-red-900/40 text-red-400
                            @elseif($maintenance->priority === 'high') bg-orange-900/40 text-orange-400
                            @elseif($maintenance->priority === 'medium') bg-yellow-900/40 text-yellow-400
                            @else bg-slate-700 text-slate-400 @endif">
                            {{ ucfirst($maintenance->priority) }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($maintenance->status === 'open') bg-yellow-900/40 text-yellow-400
                            @elseif($maintenance->status === 'in_progress') bg-[#001A3A]/40 text-[#0078D4]
                            @elseif($maintenance->status === 'resolved') bg-emerald-900/40 text-emerald-400
                            @else bg-slate-700 text-slate-400 @endif">
                            {{ ucfirst(str_replace('_', ' ', $maintenance->status)) }}
                        </span>
                    </div>
                    <a href="{{ route('maintenance.index') }}" class="text-sm text-slate-400 hover:text-white mt-0.5 inline-block">&larr; Maintenance</a>
                </div>
                <a href="{{ route('maintenance.edit', $maintenance) }}"
                   class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
            </div>
        </div>

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
                        <a href="{{ route('renters.show', $maintenance->renter) }}" class="hover:text-[#0078D4]">{{ $maintenance->renter->name }}</a>
                    </p>
                </div>
                @endif
                @if($maintenance->lease)
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Lease</p>
                    <p class="text-slate-200 mt-0.5">
                        <a href="{{ route('leases.show', $maintenance->lease) }}" class="hover:text-[#0078D4]">Lease #{{ $maintenance->lease->id }}</a>
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

        {{-- Photos --}}
        <div class="bg-slate-800 rounded-xl p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-300">Photos</h3>

            @if($maintenance->photos->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($maintenance->photos as $photo)
                        <div class="relative group">
                            <img src="{{ $photo->url() }}" alt="{{ $photo->caption ?? $maintenance->title }}"
                                 class="w-full h-32 object-cover rounded-lg border border-slate-700">
                            <form method="POST" action="{{ route('maintenance.photos.destroy', [$maintenance, $photo]) }}"
                                  class="absolute top-1.5 right-1.5"
                                  onsubmit="return confirm('Remove this photo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-900/80 text-slate-300 hover:bg-red-800/80 hover:text-white text-xs leading-none">
                                    &times;
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">No photos yet &mdash; useful for before/after or proof of completion.</p>
            @endif

            <form method="POST" action="{{ route('maintenance.photos.store', $maintenance) }}"
                  enctype="multipart/form-data" class="pt-2 border-t border-slate-700 flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="photos[]" multiple accept="image/png,image/jpeg,image/webp" required
                       class="text-sm text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 file:text-sm hover:file:bg-slate-600">
                <button type="submit" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">
                    Add Photos
                </button>
            </form>
        </div>

        {{-- Contractor --}}
        <div class="bg-slate-800 rounded-xl p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-300">Contractor</h3>

            @if($maintenance->contractor)
                <div class="p-3 bg-emerald-900/20 border border-emerald-800 rounded-lg flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-emerald-400 uppercase font-semibold">Assigned To</p>
                        <p class="text-slate-100 text-sm mt-0.5">{{ $maintenance->contractor->name }}{{ $maintenance->contractor->company_name ? ' (' . $maintenance->contractor->company_name . ')' : '' }}</p>
                    </div>
                    <a href="{{ route('contractors.show', $maintenance->contractor) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] whitespace-nowrap">View profile &rarr;</a>
                </div>
            @endif

            <form method="POST" action="{{ route('maintenance.assign-contractors', $maintenance) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-2">
                        Invite contractors to quote {{ $maintenance->contractor ? '(re-inviting will not change who\'s already assigned)' : '— whichever quote gets approved becomes the assigned contractor' }}
                    </label>
                    <div class="flex flex-wrap gap-3">
                        @forelse($contractors as $c)
                            <label class="flex items-center gap-2 px-3 py-1.5 bg-slate-700 rounded-lg text-sm text-slate-200 cursor-pointer">
                                <input type="checkbox" name="contractor_ids[]" value="{{ $c->id }}"
                                       @checked($maintenance->invitedContractors->contains($c->id))
                                       class="rounded border-slate-500 bg-slate-800">
                                {{ $c->name }}{{ $c->company_name ? ' (' . $c->company_name . ')' : '' }}
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">No active contractors yet — <a href="{{ route('contractors.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0]">add one</a>.</p>
                        @endforelse
                    </div>
                </div>
                @if($contractors->isNotEmpty())
                    <button type="submit" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm rounded-lg">Save Invitations</button>
                @endif
            </form>

            @if($maintenance->quotes->isNotEmpty())
                <div class="pt-3 border-t border-slate-700 space-y-3">
                    @foreach($maintenance->quotes as $quote)
                        <div class="p-3 bg-slate-900 rounded-lg">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-200 font-medium">{{ $quote->contractor?->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">R{{ number_format($quote->amount, 2) }}{{ $quote->notes ? ' — ' . $quote->notes : '' }}</p>
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
                                <div class="flex gap-2 mt-2">
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
                                <form method="POST" action="{{ route('maintenance-quotes.mark-paid', $quote) }}" class="mt-2 flex gap-2">
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

        {{-- Status Update Form --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Update Status</h3>
            <form method="POST" action="{{ route('maintenance.status', $maintenance) }}" class="space-y-4">
                @csrf
                @method('PATCH')

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
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-semibold">
                        Update Status
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
