@php $editing = isset($module); @endphp

<div class="px-6 py-5 space-y-5">

    @if ($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Key (create only) --}}
    @if (!$editing)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Module Key <span class="text-red-500">*</span></label>
        <input type="text" name="key" value="{{ old('key') }}"
               placeholder="e.g. crm, loyalty, payroll"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('key') border-red-400 @enderror">
        <p class="mt-1 text-xs text-gray-400">Lowercase, underscores only. Matches the config/middleware key. Cannot be changed after creation.</p>
    </div>
    @else
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Module Key</label>
        <p class="font-mono text-sm text-gray-500 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">{{ $module->key }}</p>
    </div>
    @endif

    {{-- Name --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $module->name ?? '') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
        <textarea name="description" rows="3"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror">{{ old('description', $module->description ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-400">Shown on the public welcome page. Max 500 characters.</p>
    </div>

    {{-- Icon & Price --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Icon Key</label>
            <input type="text" name="icon" value="{{ old('icon', $module->icon ?? 'chart') }}"
                   placeholder="calendar, pos, store, building…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="mt-1 text-xs text-gray-400">calendar · pos · store · building · chart · widget · domain · star · users · map</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Price (ZAR) <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm">R</span>
                <input type="number" name="price" value="{{ old('price', $module->price ?? '') }}"
                       min="0" step="0.01"
                       class="w-full border border-gray-300 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('price') border-red-400 @enderror">
            </div>
        </div>
    </div>

    {{-- Status & Launch Date --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select name="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="active"      {{ old('status', $module->status ?? '') === 'active'      ? 'selected' : '' }}>Live</option>
                <option value="beta"        {{ old('status', $module->status ?? '') === 'beta'        ? 'selected' : '' }}>In Testing (Beta)</option>
                <option value="coming_soon" {{ old('status', $module->status ?? '') === 'coming_soon' ? 'selected' : '' }}>Coming Soon</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Launch Date</label>
            <input type="date" name="launch_date"
                   value="{{ old('launch_date', isset($module->launch_date) ? $module->launch_date->format('Y-m-d') : '') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <p class="mt-1 text-xs text-gray-400">Optional — displayed for coming-soon modules.</p>
        </div>
    </div>

    {{-- Sort Order --}}
    <div class="w-1/3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $module->sort_order ?? 0) }}"
               min="0"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p class="mt-1 text-xs text-gray-400">Lower numbers appear first.</p>
    </div>

    {{-- Toggles --}}
    <div class="flex items-start gap-8 pt-1">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_visible" value="0">
            <input type="checkbox" name="is_visible" value="1"
                   {{ old('is_visible', $module->is_visible ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Visible on welcome page</span>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="auto_activate" value="0">
            <input type="checkbox" name="auto_activate" value="1"
                   {{ old('auto_activate', $module->auto_activate ?? false) ? 'checked' : '' }}
                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Auto-activate on request</span>
        </label>
    </div>

</div>
