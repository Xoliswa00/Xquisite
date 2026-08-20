<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">New Gig — Discovery</h2>
            <a href="{{ route('gigs.index') }}" class="text-sm text-ink-muted hover:text-ink">&larr; Back to Gigs</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('gigs.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">Client &amp; Category</h3>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Client *</label>
                    <select name="client_id" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        <option value="">Select client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-ink-faint mt-1">No client yet? <a href="{{ route('clients.create') }}" class="text-[#0078D4]">Add one first</a>.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Service Line *</label>
                    <select name="category" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        <option value="software_solutions" @selected(old('category') === 'software_solutions')>Software Solutions</option>
                        <option value="business_automation" @selected(old('category') === 'business_automation')>Business Automation</option>
                        <option value="data_intelligence" @selected(old('category') === 'data_intelligence')>Data &amp; Intelligence</option>
                        <option value="digital_solutions" @selected(old('category', 'digital_solutions') === 'digital_solutions')>Digital Solutions</option>
                        <option value="other" @selected(old('category') === 'other')>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. New business website"
                           class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>
            </div>

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">Discovery</h3>
                <p class="text-xs text-ink-faint">What did the client say they need? Capture the conversation here — you'll turn it into a priced quote afterward.</p>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Discovery Notes</label>
                    <textarea name="discovery_notes" rows="5" placeholder="Goals, must-have pages/features, target audience, timeline, budget hints..."
                              class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">{{ old('discovery_notes') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Description (short summary)</label>
                    <textarea name="description" rows="2" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">Scheduling &amp; Rate</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Target Deadline</label>
                        <input type="date" name="deadline_date" value="{{ old('deadline_date') }}" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Out-of-scope Hourly Rate (R)</label>
                        <input type="number" name="hourly_rate" value="{{ old('hourly_rate') }}" step="0.01" min="0"
                               placeholder="e.g. 450–750"
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="bg-panel rounded-xl p-4 text-xs text-ink-faint border border-line-2">
                Creating a gig starts it as a <strong>Lead</strong>. Once you've scoped it, build a quote from the gig page to price and send it to the client.
            </div>

            <div class="flex justify-between">
                <a href="{{ route('gigs.index') }}" class="px-5 py-2 bg-panel-2 hover:bg-line-2 text-ink-muted rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white rounded-lg text-sm font-semibold">Create Gig</button>
            </div>
        </form>
    </div>
</x-app-layout>
