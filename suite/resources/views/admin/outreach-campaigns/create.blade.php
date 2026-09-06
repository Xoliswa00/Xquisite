<x-app-layout>
    <x-slot name="header">New Outreach Campaign</x-slot>

    <div class="max-w-xl space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#D4AF37]">New Outreach Campaign</h2>
            <p class="text-slate-400 text-sm mt-1">Plan a wave before it starts — link its URL with <code class="text-slate-300">?campaign={id}</code> once created.</p>
        </div>

        <form method="POST" action="{{ route('admin.outreach-campaigns.store') }}" class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. TikTok Wave 2 — Cape Town nail salons"
                       class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-500 rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Channel</label>
                    <input type="text" name="channel" value="{{ old('channel') }}" placeholder="e.g. tiktok, whatsapp, referral"
                           class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-500 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Target industry</label>
                    <select name="target_business_type" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                        <option value="">Any</option>
                        @foreach(['salon' => 'Salon', 'beauty' => 'Beauty', 'wellness' => 'Wellness/Spa', 'fitness' => 'Fitness', 'service' => 'Other service business', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('target_business_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Planned count</label>
                    <input type="number" min="1" name="planned_count" value="{{ old('planned_count') }}" placeholder="e.g. 5"
                           class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-500 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                    <select name="status" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                        @foreach(['planned' => 'Planned', 'active' => 'Active', 'completed' => 'Completed'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'planned') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Start date</label>
                <input type="date" name="started_at" value="{{ old('started_at') }}" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-medium transition">
                    Create campaign
                </button>
                <a href="{{ route('admin.outreach-campaigns.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
