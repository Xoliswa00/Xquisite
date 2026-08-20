<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Edit Gig</h2>
            <a href="{{ route('gigs.show', $gig) }}" class="text-sm text-ink-muted hover:text-ink">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('gigs.update', $gig) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <p class="text-xs text-ink-faint">Client: <span class="text-ink font-medium">{{ $gig->client?->name }}</span> — can't be changed after creation.</p>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Service Line *</label>
                    <select name="category" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        <option value="software_solutions" @selected($gig->category === 'software_solutions')>Software Solutions</option>
                        <option value="business_automation" @selected($gig->category === 'business_automation')>Business Automation</option>
                        <option value="data_intelligence" @selected($gig->category === 'data_intelligence')>Data &amp; Intelligence</option>
                        <option value="digital_solutions" @selected($gig->category === 'digital_solutions')>Digital Solutions</option>
                        <option value="other" @selected($gig->category === 'other')>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $gig->title) }}" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Discovery Notes</label>
                    <textarea name="discovery_notes" rows="4" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">{{ old('discovery_notes', $gig->discovery_notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">{{ old('description', $gig->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Target Deadline</label>
                        <input type="date" name="deadline_date" value="{{ old('deadline_date', $gig->deadline_date?->toDateString()) }}" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Out-of-scope Hourly Rate (R)</label>
                        <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $gig->hourly_rate) }}" step="0.01" min="0" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">{{ old('notes', $gig->notes) }}</textarea>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('gigs.show', $gig) }}" class="px-5 py-2 bg-panel-2 hover:bg-line-2 text-ink-muted rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white rounded-lg text-sm font-semibold">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
