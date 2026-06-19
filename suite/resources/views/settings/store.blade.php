<x-app-layout>
    <x-slot name="header">Store Settings</x-slot>

    <div class="max-w-xl">
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-base font-semibold text-slate-100 mb-1">Shipping</h3>
            <p class="text-xs text-slate-400 mb-5">
                Controls the delivery fee added at online checkout. Collection orders are always free.
            </p>

            <form method="POST" action="{{ route('store.settings.update') }}" class="space-y-5"
                  x-data="{ enabled: {{ old('shipping_enabled', $tenant->shipping_enabled) ? 'true' : 'false' }},
                            type: '{{ old('shipping_type', $tenant->shipping_type ?? 'flat') }}' }">
                @csrf
                @method('PATCH')

                {{-- Enable shipping --}}
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="shipping_enabled" value="1" x-model="enabled"
                           class="mt-0.5 w-4 h-4 rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <span>
                        <span class="block text-sm font-medium text-slate-200">Charge for delivery</span>
                        <span class="block text-xs text-slate-400">When off, delivery orders are not charged a shipping fee.</span>
                    </span>
                </label>

                {{-- Shipping type + cost (only relevant when enabled) --}}
                <div x-show="enabled" x-cloak class="space-y-5 pl-7">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Shipping type</label>
                        <select name="shipping_type" x-model="type"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('shipping_type') border-red-500 @enderror">
                            <option value="flat">Flat rate</option>
                            <option value="free">Free delivery</option>
                        </select>
                        @error('shipping_type')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="type === 'flat'" x-cloak>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Flat delivery fee (R)</label>
                        <input type="number" name="shipping_cost" step="0.01" min="0"
                               value="{{ old('shipping_cost', $tenant->shipping_cost) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('shipping_cost') border-red-500 @enderror">
                        @error('shipping_cost')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                        Save settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
