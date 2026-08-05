<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('success')) <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div> @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-ink">Link your socials</h2>
                <p class="text-sm text-ink-muted mt-1">Shown as icons in your site footer. Leave any blank you don't use.</p>
            </div>

            <form method="POST" action="{{ route('website.branding.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach (['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube'] as $platform)
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-ink-faint w-16 shrink-0 capitalize">{{ $platform }}</span>
                            <input type="url" name="socials[{{ $platform }}]"
                                   value="{{ old('socials.' . $platform, $branding->socials[$platform] ?? '') }}"
                                   placeholder="https://…"
                                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                    Continue
                </button>
            </form>
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
