<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('success')) <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div> @endif
        @if (session('info')) <div class="px-4 py-3 rounded-xl bg-[#0078D4]/10 border border-[#0078D4]/30 text-[#0078D4] text-sm">{{ session('info') }}</div> @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-ink">Choose your template</h2>
                <p class="text-sm text-ink-muted mt-1">Browse the full catalog — filter by industry, preview live, and pick the look for your site.</p>
            </div>

            @if ($activeTemplate && ! $activeTemplate->isPlaceholder())
                <div class="flex items-center gap-4 p-4 rounded-lg bg-panel border border-line">
                    <x-template-preview :template="$activeTemplate" ratio="aspect-video" class="w-28 shrink-0 border border-line" />
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-400">Live now</p>
                        <p class="text-sm font-medium text-ink mt-0.5">{{ $activeTemplate->name }}</p>
                    </div>
                </div>
            @elseif ($preferredTemplate)
                <div class="flex items-center gap-4 p-4 rounded-lg bg-panel border border-line">
                    <x-template-preview :template="$preferredTemplate" ratio="aspect-video" class="w-28 shrink-0 border border-line" />
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Your pick</p>
                        <p class="text-sm font-medium text-ink mt-0.5">{{ $preferredTemplate->name }}</p>
                    </div>
                </div>
            @else
                <p class="text-sm text-ink-faint">No template chosen yet.</p>
            @endif

            <a href="{{ route('website.templates.index', ['from_wizard' => 1]) }}"
               class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                Browse Templates
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
