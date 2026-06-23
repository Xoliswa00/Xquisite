<x-app-layout>
    <x-slot name="header">{{ isset($promotion) ? 'Edit Promotion' : 'New Promotion' }}</x-slot>

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ isset($promotion) ? route('promotions.update', $promotion) : route('promotions.store') }}" class="space-y-5">
            @csrf
            @if(isset($promotion)) @method('PUT') @endif
            <x-form-errors />

            {{-- Online-only notice --}}
            <div class="flex items-start gap-3 bg-amber-950/40 border border-amber-800/50 rounded-xl px-4 py-3">
                <svg class="shrink-0 w-4 h-4 text-amber-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-400">Online booking portal only</p>
                    <p class="text-xs text-amber-400/70 mt-0.5">
                        Promo codes are redeemable exclusively through the self-service booking portal.
                        Phone, DM, or in-person bookings do not qualify — the code will be forfeited if a client requests it outside the portal.
                    </p>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="font-semibold text-[#D4AF37]">Promotion Details</h3>

                <div>
                    <x-input-label value="Name" />
                    <x-text-input name="name" class="mt-1 w-full" value="{{ old('name', $promotion->name ?? '') }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label value="Description" />
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm focus:ring-[#0078D4] focus:border-[#0078D4]">{{ old('description', $promotion->description ?? '') }}</textarea>
                </div>

                <div>
                    <x-input-label value="Promo Code" />
                    <div class="flex gap-2 mt-1">
                        <x-text-input name="code" id="promo-code" class="flex-1" value="{{ old('code', $promotion->code ?? '') }}" required />
                        <button type="button" id="gen-code-btn"
                                class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors">
                            Generate
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('code')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Discount Type" />
                        <select name="discount_type" class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm">
                            <option value="percentage" {{ old('discount_type', $promotion->discount_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('discount_type', $promotion->discount_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed (R)</option>
                        </select>
                        <x-input-error :messages="$errors->get('discount_type')" />
                    </div>
                    <div>
                        <x-input-label value="Discount Value" />
                        <x-text-input name="discount_value" type="number" step="0.01" min="0" class="mt-1 w-full" value="{{ old('discount_value', $promotion->discount_value ?? '') }}" required />
                        <x-input-error :messages="$errors->get('discount_value')" />
                    </div>
                </div>

                <div>
                    <x-input-label value="Applies To" />
                    <div class="mt-2 flex gap-4" id="applies-to-group">
                        @foreach(['all' => 'All', 'services' => 'Services (bookings)', 'products' => 'Products (shop)'] as $val => $label)
                            <label class="applies-option flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-700 cursor-pointer text-sm text-slate-300 hover:bg-slate-800 has-[:checked]:border-[#0078D4] has-[:checked]:text-[#B8D4F0]">
                                <input type="radio" name="applies_to" value="{{ $val }}" {{ old('applies_to', $promotion->applies_to ?? 'all') === $val ? 'checked' : '' }} class="text-[#0078D4]">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Valid From" />
                        <x-text-input name="valid_from" type="datetime-local" class="mt-1 w-full" value="{{ old('valid_from', isset($promotion->valid_from) ? $promotion->valid_from->format('Y-m-d\TH:i') : '') }}" />
                        <x-input-error :messages="$errors->get('valid_from')" />
                    </div>
                    <div>
                        <x-input-label value="Valid Until" />
                        <x-text-input name="valid_until" type="datetime-local" class="mt-1 w-full" value="{{ old('valid_until', isset($promotion->valid_until) ? $promotion->valid_until->format('Y-m-d\TH:i') : '') }}" />
                        <x-input-error :messages="$errors->get('valid_until')" />
                    </div>
                </div>

                <div>
                    <x-input-label value="Max Uses (optional)" />
                    <x-text-input name="max_uses" type="number" min="1" class="mt-1 w-full" value="{{ old('max_uses', $promotion->max_uses ?? '') }}" placeholder="Unlimited" />
                    <x-input-error :messages="$errors->get('max_uses')" />
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-[#0078D4]">
                    <span class="text-sm text-slate-300">Active</span>
                </label>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="px-6 py-2.5 bg-[#0078D4] hover:bg-[#0078D4] text-white font-medium rounded-lg text-sm transition-colors">
                    {{ isset($promotion) ? 'Update' : 'Create Promotion' }}
                </button>
                <a href="{{ route('promotions.index') }}" class="px-6 py-2.5 border border-slate-700 text-slate-300 hover:bg-slate-800 rounded-lg text-sm transition-colors text-center">Cancel</a>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('gen-code-btn').addEventListener('click', function() {
        fetch('{{ route('promotions.generate-code') }}')
            .then(r => r.json())
            .then(d => { document.getElementById('promo-code').value = d.code; });
    });
    </script>
</x-app-layout>
