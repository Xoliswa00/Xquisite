@php $editing = isset($template); @endphp

<div class="px-6 py-5 space-y-5">

    @if ($errors->any())
        <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    {{-- Key (create only) --}}
    @if (!$editing)
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Template Key <span class="text-red-400">*</span></label>
        <input type="text" name="key" value="{{ old('key') }}"
               placeholder="e.g. eat-restaurant, add-life-fitness"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('key') border-red-500 @enderror">
        <p class="mt-1 text-xs text-slate-500">Lowercase, dashes only. Cannot be changed after creation.</p>
    </div>
    @else
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Template Key</label>
        <p class="font-mono text-sm text-slate-400 px-3 py-2 bg-slate-900/50 border border-slate-700 rounded-lg">{{ $template->key }}</p>
    </div>
    @endif

    {{-- Name --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Name <span class="text-red-400">*</span></label>
        <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
        <textarea name="description" rows="3"
                  class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('description') border-red-500 @enderror">{{ old('description', $template->description ?? '') }}</textarea>
        <p class="mt-1 text-xs text-slate-500">Shown to tenants browsing the catalog. Max 1000 characters.</p>
    </div>

    {{-- Category & Blade view --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Category</label>
            <input type="text" name="category" value="{{ old('category', $template->category ?? '') }}"
                   placeholder="restaurant, fitness, coming-soon…"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Blade View <span class="text-red-400">*</span></label>
            <input type="text" name="blade_view" value="{{ old('blade_view', $template->blade_view ?? '') }}"
                   placeholder="site-templates.eat-restaurant"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('blade_view') border-red-500 @enderror">
            <p class="mt-1 text-xs text-slate-500">Dot-notation view path that renders this template.</p>
        </div>
    </div>

    {{-- Preview image --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Preview Image URL</label>
        <input type="text" name="preview_image_url" value="{{ old('preview_image_url', $template->preview_image_url ?? '') }}"
               placeholder="/site-templates/eat-restaurant/preview.jpg"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
    </div>

    {{-- Version, author & release notes --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Version</label>
            <input type="text" name="version" value="{{ old('version', $template->version ?? '1.0.0') }}"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Author</label>
            <input type="text" name="author" value="{{ old('author', $template->author ?? 'Xquisite') }}"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Release Notes</label>
        <textarea name="release_notes" rows="2" placeholder="What changed in this version — leave blank to skip logging a changelog entry."
                  class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('release_notes') }}</textarea>
        <p class="mt-1 text-xs text-slate-500">If filled in, this is saved as a new entry (dated today, tagged with the Version above) in the template's Update History shown to tenants.</p>

        @if ($editing && !empty($template->changelog))
            <div class="mt-3 space-y-1.5 max-h-40 overflow-y-auto rounded-lg border border-slate-700 bg-slate-900/50 p-3">
                @foreach ($template->changelog as $entry)
                    <p class="text-xs text-slate-400"><span class="font-mono text-slate-300">v{{ $entry['version'] ?? '?' }}</span> · {{ $entry['date'] ?? '' }} — {{ $entry['notes'] ?? '' }}</p>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modules supported --}}
    @if (isset($modules) && $modules->count())
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-2">Modules Supported</label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @php $selectedModules = old('modules_supported', $template->modules_supported ?? []); @endphp
            @foreach ($modules as $module)
                <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-300">
                    <input type="checkbox" name="modules_supported[]" value="{{ $module->key }}"
                           {{ in_array($module->key, $selectedModules) ? 'checked' : '' }}
                           class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    {{ $module->name }}
                </label>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Scores --}}
    <div>
        <div class="flex items-center justify-between mb-1">
            <label class="block text-sm font-medium text-slate-300">Speed / SEO / Accessibility Scores</label>
            @if ($editing)
                <form method="POST" action="{{ route('admin.templates.refresh-scores', $template) }}">
                    @csrf
                    <button type="submit" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium">Refresh via PageSpeed Insights</button>
                </form>
            @endif
        </div>
        <p class="mb-2 text-xs text-slate-500">
            0-100. Refresh only works once this template's preview URL is publicly reachable (not localhost) — otherwise enter scores manually from a Lighthouse run.
            @if ($editing && $template->scores_checked_at)
                Last auto-checked {{ $template->scores_checked_at->diffForHumans() }}.
            @endif
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <span class="block text-xs text-slate-400 mb-1">Speed</span>
                <input type="number" name="speed_score" value="{{ old('speed_score', $template->speed_score ?? '') }}" min="0" max="100"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>
            <div>
                <span class="block text-xs text-slate-400 mb-1">SEO</span>
                <input type="number" name="seo_score" value="{{ old('seo_score', $template->seo_score ?? '') }}" min="0" max="100"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>
            <div>
                <span class="block text-xs text-slate-400 mb-1">Accessibility</span>
                <input type="number" name="accessibility_score" value="{{ old('accessibility_score', $template->accessibility_score ?? '') }}" min="0" max="100"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>
        </div>
    </div>

    {{-- Rating override --}}
    <div class="w-full sm:w-1/3">
        <label class="block text-sm font-medium text-slate-300 mb-1">Rating Override</label>
        <input type="number" name="rating_override" value="{{ old('rating_override', $template->rating_override ?? '') }}" min="0" max="5" step="0.1"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
        <p class="mt-1 text-xs text-slate-500">
            0-5 stars. Shown instead of the real tenant-review average when set
            @if ($editing && $template->rating_count)
                (currently {{ $template->rating_count }} real {{ Str::plural('review', $template->rating_count) }} averaging {{ $template->rating_avg }})
            @endif
            — leave blank to show the real average once you have reviews.
        </p>
    </div>

    {{-- Price type & Price --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Price Type <span class="text-red-400">*</span></label>
            <select name="price_type"
                    class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                @foreach (['free' => 'Free', 'one_time' => 'One-time', 'monthly' => 'Monthly subscription', 'annual' => 'Annual subscription'] as $value => $label)
                    <option value="{{ $value }}" {{ old('price_type', $template->price_type ?? 'free') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Only "Free" templates are activatable by tenants until card payments launch.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Price (ZAR)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">R</span>
                <input type="number" name="price" value="{{ old('price', $template->price ?? '') }}"
                       min="0" step="0.01"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('price') border-red-500 @enderror">
            </div>
        </div>
    </div>

    {{-- Default colors --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Default Colors</label>
        <p class="mb-2 text-xs text-slate-500">Shown until a tenant sets their own branding colors. Leave blank to fall back to Xquisite's default blue/navy/gold.</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <span class="block text-xs text-slate-400 mb-1">Primary</span>
                <input type="text" name="default_primary_color" value="{{ old('default_primary_color', $template->default_primary_color ?? '') }}"
                       placeholder="#0078D4" maxlength="7"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>
            <div>
                <span class="block text-xs text-slate-400 mb-1">Secondary</span>
                <input type="text" name="default_secondary_color" value="{{ old('default_secondary_color', $template->default_secondary_color ?? '') }}"
                       placeholder="#002B5B" maxlength="7"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>
            <div>
                <span class="block text-xs text-slate-400 mb-1">Accent</span>
                <input type="text" name="default_accent_color" value="{{ old('default_accent_color', $template->default_accent_color ?? '') }}"
                       placeholder="#D4AF37" maxlength="7"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>
        </div>
    </div>

    {{-- Sort Order --}}
    <div class="w-full sm:w-1/3">
        <label class="block text-sm font-medium text-slate-300 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $template->sort_order ?? 0) }}"
               min="0"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
        <p class="mt-1 text-xs text-slate-500">Lower numbers appear first.</p>
    </div>

    {{-- Toggles --}}
    <div class="flex flex-wrap items-center gap-6 pt-1">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
            <span class="text-sm text-slate-300">Active in catalog</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_visible" value="0">
            <input type="checkbox" name="is_visible" value="1"
                   {{ old('is_visible', $template->is_visible ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
            <span class="text-sm text-slate-300">Visible to tenants</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1"
                   {{ old('is_featured', $template->is_featured ?? false) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
            <span class="text-sm text-slate-300">Featured</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="supports_theme_toggle" value="0">
            <input type="checkbox" name="supports_theme_toggle" value="1"
                   {{ old('supports_theme_toggle', $template->supports_theme_toggle ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
            <span class="text-sm text-slate-300">Supports light/dark toggle</span>
        </label>
    </div>

</div>
