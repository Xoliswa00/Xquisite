<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6"
         x-data="{
             primary: '{{ old('primary_color', $branding->primary_color ?? '#0078D4') }}',
             secondary: '{{ old('secondary_color', $branding->secondary_color ?? '#002B5B') }}',
             accent: '{{ old('accent_color', $branding->accent_color ?? '#D4AF37') }}',
         }">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('success')) <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div> @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-ink">Choose your colours</h2>
                <p class="text-sm text-ink-muted mt-1">Used across your site — buttons, headings, and accents.</p>
            </div>

            <form method="POST" action="{{ route('website.branding.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-muted mb-1">Primary</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="primary" class="w-10 h-10 rounded-lg bg-panel border border-line-2 cursor-pointer">
                            <input type="text" name="primary_color" x-model="primary" maxlength="7"
                                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-muted mb-1">Secondary</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="secondary" class="w-10 h-10 rounded-lg bg-panel border border-line-2 cursor-pointer">
                            <input type="text" name="secondary_color" x-model="secondary" maxlength="7"
                                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-muted mb-1">Accent</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="accent" class="w-10 h-10 rounded-lg bg-panel border border-line-2 cursor-pointer">
                            <input type="text" name="accent_color" x-model="accent" maxlength="7"
                                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                    Continue
                </button>
            </form>
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
