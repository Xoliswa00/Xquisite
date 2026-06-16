@php $editing = isset($service); @endphp

<div class="px-6 py-5 space-y-5">

    @if ($errors->any())
        <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
            @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    {{-- Key --}}
    @if (!$editing)
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Service Key <span class="text-red-400">*</span></label>
        <input type="text" name="key" value="{{ old('key') }}" placeholder="e.g. onboarding_standard"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
    </div>
    @else
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Service Key</label>
        <p class="font-mono text-sm text-slate-400 px-3 py-2 bg-slate-900/50 border border-slate-700 rounded-lg">{{ $service->key }}</p>
    </div>
    @endif

    {{-- Name --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Name <span class="text-red-400">*</span></label>
        <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}"
               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Description <span class="text-red-400">*</span></label>
        <textarea name="description" rows="3"
                  class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description', $service->description ?? '') }}</textarea>
    </div>

    {{-- Category + Billing type --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Category <span class="text-red-400">*</span></label>
            <select name="category"
                    class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                @foreach (['onboarding' => 'Onboarding', 'training' => 'Training', 'support' => 'Support', 'custom' => 'Custom'] as $val => $label)
                    <option value="{{ $val }}" {{ old('category', $service->category ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Billing Type <span class="text-red-400">*</span></label>
            <select name="billing_type"
                    class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="once_off"  {{ old('billing_type', $service->billing_type ?? '') === 'once_off'  ? 'selected' : '' }}>Once-off</option>
                <option value="recurring" {{ old('billing_type', $service->billing_type ?? '') === 'recurring' ? 'selected' : '' }}>Recurring (monthly)</option>
            </select>
        </div>
    </div>

    {{-- Price + Label --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Price (ZAR)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">R</span>
                <input type="number" name="price" min="0" step="0.01"
                       value="{{ old('price', $service->price ?? '') }}"
                       placeholder="Leave blank for custom quote"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Price Label Override</label>
            <input type="text" name="price_label" value="{{ old('price_label', $service->price_label ?? '') }}"
                   placeholder="e.g. From R800 or Custom quote"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <p class="mt-1 text-xs text-slate-500">Overrides the price display. Leave blank to auto-format.</p>
        </div>
    </div>

    {{-- Icon + Sort --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Icon</label>
            <input type="text" name="icon" value="{{ old('icon', $service->icon ?? 'wrench') }}"
                   placeholder="rocket · upload · academic · shield · code · chart"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Sort Order</label>
            <input type="number" name="sort_order" min="0"
                   value="{{ old('sort_order', $service->sort_order ?? 0) }}"
                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Toggles --}}
    <div class="flex flex-wrap items-center gap-6 pt-1">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500">
            <span class="text-sm text-slate-300">Active (visible to clients)</span>
        </label>
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_requestable" value="0">
            <input type="checkbox" name="is_requestable" value="1"
                   {{ old('is_requestable', $service->is_requestable ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500">
            <span class="text-sm text-slate-300">Clients can self-request</span>
        </label>
    </div>

</div>
