@php $editing = isset($plan); @endphp

<div class="px-6 py-5 space-y-5">

    @if ($errors->any())
        <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
            @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    {{-- Key (create only) --}}
    @if (!$editing)
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Plan Key <span class="text-red-400">*</span></label>
        <input type="text" name="key" value="{{ old('key') }}" placeholder="e.g. starter, growth, scale"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        <p class="mt-1 text-xs text-slate-500">Lowercase, underscores only. Cannot be changed after creation.</p>
    </div>
    @else
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Plan Key</label>
        <p class="font-mono text-sm text-slate-400 px-3 py-2 bg-slate-900/50 border border-slate-700 rounded-lg">{{ $plan->key }}</p>
    </div>
    @endif

    {{-- Name + Tagline --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Name <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Tagline</label>
            <input type="text" name="tagline" value="{{ old('tagline', $plan->tagline ?? '') }}"
                   placeholder="One-line pitch shown on pricing card"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
        <textarea name="description" rows="2"
                  class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description', $plan->description ?? '') }}</textarea>
    </div>

    {{-- Pricing --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Monthly Price (ZAR) <span class="text-red-400">*</span></label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">R</span>
                <input type="number" name="price_monthly" min="0" step="0.01"
                       value="{{ old('price_monthly', $plan->price_monthly ?? '') }}"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Annual Price (ZAR/mo)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">R</span>
                <input type="number" name="price_annual" min="0" step="0.01"
                       value="{{ old('price_annual', $plan->price_annual ?? '') }}"
                       placeholder="Leave blank to hide"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <p class="mt-1 text-xs text-slate-500">Billed × 12 annually. Shows discount % on pricing page.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Sort Order</label>
            <input type="number" name="sort_order" min="0"
                   value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Modules --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-2">Included Modules</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($availableModules as $module)
            <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-slate-700 hover:border-indigo-500 cursor-pointer transition-colors">
                <input type="checkbox" name="modules[]" value="{{ $module->key }}"
                       {{ in_array($module->key, old('modules', $selectedModules ?? [])) ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500">
                <div>
                    <p class="text-sm font-medium text-slate-200">{{ $module->name }}</p>
                    <p class="text-xs text-slate-500">R{{ number_format($module->price, 0) }}/mo individually</p>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Toggles --}}
    <div class="flex flex-wrap items-center gap-6 pt-1">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500">
            <span class="text-sm text-slate-300">Active (visible on pricing page)</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1"
                   {{ old('is_featured', $plan->is_featured ?? false) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500">
            <span class="text-sm text-slate-300">Featured (highlighted as recommended)</span>
        </label>
    </div>

</div>
