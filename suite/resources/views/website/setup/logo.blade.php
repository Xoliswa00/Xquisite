<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('status') === 'logo-updated')
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">Logo updated.</div>
        @endif
        @if ($errors->any())
            <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
                @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
            </div>
        @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-ink">Upload your logo</h2>
                <p class="text-sm text-ink-muted mt-1">Shown in your site header and browser tab. JPEG, PNG, WebP or SVG — max 2 MB.</p>
            </div>

            <form method="POST" action="{{ route('profile.logo.update') }}" enctype="multipart/form-data" class="flex items-center gap-6">
                @csrf
                <div class="shrink-0">
                    @if ($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="Logo" class="w-16 h-16 rounded-lg object-cover border border-line bg-panel">
                    @else
                        <div class="w-16 h-16 rounded-lg border-2 border-dashed border-line-2 bg-panel"></div>
                    @endif
                </div>
                <div class="flex-1 space-y-2" x-data="{ fileName: '' }">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-panel hover:bg-line border border-line-2 text-sm text-ink-muted transition">
                            Choose file
                        </span>
                        <span class="text-sm text-ink-faint" x-text="fileName || 'No file selected'"></span>
                        <input type="file" name="logo" accept="image/*" class="sr-only" @change="fileName = $event.target.files[0]?.name || ''">
                    </label>
                    @error('logo')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">Upload</button>
            </form>
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
