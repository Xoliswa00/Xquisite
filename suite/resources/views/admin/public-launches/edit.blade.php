<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-[#D4AF37]">Edit — {{ $launch->title }}</h2>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <a href="{{ route('admin.public-launches.index') }}" class="text-sm text-slate-400 hover:text-white transition">&larr; Back to launch pages</a>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl shadow-black/25">
            <form method="POST" action="{{ route('admin.public-launches.update', $launch) }}" class="px-8 py-8 space-y-6">
                @csrf
                @method('PATCH')
                <x-form-errors />

                <div class="space-y-1.5">
                    <label for="title" class="text-sm font-medium text-slate-300">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $launch->title) }}" required
                           class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                </div>

                <div class="space-y-1.5">
                    <label for="tagline" class="text-sm font-medium text-slate-300">Tagline</label>
                    <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $launch->tagline) }}"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                </div>

                <div class="space-y-1.5">
                    <label for="benefits" class="text-sm font-medium text-slate-300">Benefits (one per line)</label>
                    <textarea id="benefits" name="benefits" rows="6"
                              class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition resize-none">{{ old('benefits', implode("\n", $launch->benefits ?? [])) }}</textarea>
                </div>

                <div class="space-y-1.5">
                    <label for="launch_at" class="text-sm font-medium text-slate-300">Launch date &amp; time</label>
                    <input type="datetime-local" id="launch_at" name="launch_at"
                           value="{{ old('launch_at', $launch->launch_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                    <p class="text-xs text-slate-500">Leave blank to show "Launching soon" with no countdown.</p>
                </div>

                <label class="flex items-start gap-3 rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-3.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $launch->is_active))
                           class="mt-0.5 w-4 h-4 rounded border-slate-600 bg-slate-800 text-[#0078D4] focus:ring-[#0078D4]/50">
                    <span>
                        <span class="block text-sm font-medium text-slate-200">Page is live</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Turn off to withhold this page entirely. Visitors get a 404 until it's back on.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-3.5 cursor-pointer">
                    <input type="checkbox" name="qa_enabled" value="1" @checked(old('qa_enabled', $launch->qa_enabled))
                           class="mt-0.5 w-4 h-4 rounded border-slate-600 bg-slate-800 text-[#0078D4] focus:ring-[#0078D4]/50">
                    <span>
                        <span class="block text-sm font-medium text-slate-200">Show public Q&amp;A</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Turn off to withhold just the question form and answered-questions section, keeping the rest of the page live.</span>
                    </span>
                </label>

                <div class="flex justify-end gap-3 border-t border-slate-800 pt-6">
                    <a href="{{ route('admin.public-launches.index') }}"
                       class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white bg-slate-800 border border-slate-700 hover:border-slate-500 rounded-xl transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-xl transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
