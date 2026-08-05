<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('success')) <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div> @endif
        @if (session('info')) <div class="px-4 py-3 rounded-xl bg-[#0078D4]/10 border border-[#0078D4]/30 text-[#0078D4] text-sm">{{ session('info') }}</div> @endif

        <div class="bg-panel-2 rounded-xl border border-line p-8 text-center space-y-4">

            @if (! $publishTemplate)
                <h2 class="text-lg font-semibold text-ink">Choose a template to publish</h2>
                <p class="text-sm text-ink-muted">You haven't picked a template yet.</p>
                <a href="{{ route('website.setup.show', ['step' => 'template']) }}" class="inline-block px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                    Choose a Template
                </a>

            @elseif ($alreadyLive)
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-ink">Your site is live 🎉</h2>
                <p class="text-sm text-ink-muted">{{ $publishTemplate->name }} — {{ $tenant->website_url }}</p>
                <div class="flex items-center justify-center gap-3">
                    <a href="{{ $tenant->website_url }}" target="_blank" rel="noopener" class="px-5 py-2.5 border border-line-2 text-ink text-sm font-medium rounded-lg hover:border-ink-faint transition-colors">View Live Site</a>
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">Go to Dashboard</a>
                </div>

            @elseif ($needsCheckout)
                <h2 class="text-lg font-semibold text-ink">Almost there</h2>
                <p class="text-sm text-ink-muted">{{ $publishTemplate->name }} is a premium template — purchase it to publish.</p>
                <form method="POST" action="{{ route('website.templates.checkout', $publishTemplate) }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-[#D4AF37] hover:bg-[#D4AF37]/90 text-slate-900 text-sm font-semibold rounded-lg transition-colors">
                        Buy for R{{ number_format($publishTemplate->price ?? 0, 0) }}
                    </button>
                </form>

            @else
                <h2 class="text-lg font-semibold text-ink">Ready to publish</h2>
                <p class="text-sm text-ink-muted">{{ $publishTemplate->name }} — with your logo, colours, and content.</p>

                @if ($isLocked)
                    <p class="text-xs text-[#0078D4]">Your free trial includes the Coming Soon page — {{ $publishTemplate->name }} unlocks once you're on a paid plan. Publishing now reserves it.</p>
                @endif

                <form method="POST" action="{{ route('website.templates.activate', $publishTemplate) }}">
                    @csrf
                    <input type="hidden" name="from_wizard" value="1">
                    <button type="submit" class="px-6 py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-xl transition-colors">
                        {{ $isLocked ? 'Reserve This Template' : 'Publish My Website' }}
                    </button>
                </form>
            @endif

        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
