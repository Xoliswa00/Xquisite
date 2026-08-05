<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('success')) <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div> @endif
        @if ($errors->any())
            <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
                @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
            </div>
        @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-ink">Choose your web address</h2>
                <p class="text-sm text-ink-muted mt-1">Pick a free subdomain now — connect your own domain any time.</p>
            </div>

            <form method="POST" action="{{ route('website.setup.subdomain') }}">
                @csrf
                <label class="block text-sm font-medium text-ink-muted mb-1">Subdomain</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain ?? '') }}" required
                           placeholder="yourbusiness"
                           class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    <span class="text-sm text-ink-faint shrink-0">.{{ config('app.domain', 'xquisite.co.za') }}</span>
                </div>
                <p class="mt-1 text-xs text-ink-faint">Lowercase letters, numbers, and hyphens only.</p>
                <button type="submit" class="mt-3 px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Subdomain
                </button>
            </form>
        </div>

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-ink">Custom domain (optional)</h3>
                <p class="text-xs text-ink-muted mt-1">
                    Already own a domain? Submit it here and we'll get in touch to connect it — this doesn't go live automatically.
                </p>
            </div>

            @if ($tenant->custom_domain)
                <p class="text-sm text-ink">
                    {{ $tenant->custom_domain }}
                    @if ($tenant->custom_domain_verified)
                        <span class="text-xs text-emerald-400">✓ Verified</span>
                    @else
                        <span class="text-xs text-amber-400">⚠ Pending verification</span>
                    @endif
                </p>
            @endif

            <form method="POST" action="{{ route('website.setup.custom-domain') }}" class="flex gap-2">
                @csrf
                <input type="text" name="custom_domain" value="{{ old('custom_domain') }}" placeholder="www.yourbusiness.co.za"
                       class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                <button type="submit" class="shrink-0 px-4 py-2 bg-panel hover:bg-line border border-line-2 text-ink text-sm rounded-lg transition-colors">Save</button>
            </form>
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
