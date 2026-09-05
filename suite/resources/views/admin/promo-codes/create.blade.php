<x-app-layout>
    <x-slot name="header">New Promo Code</x-slot>

    <div class="max-w-xl space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#D4AF37]">New Promo Code</h2>
            <p class="text-slate-400 text-sm mt-1">Define a discount once, then record each redemption against it to track total value given away.</p>
        </div>

        <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Code</label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="Leave blank to auto-generate" class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-500 rounded-lg text-sm uppercase">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Discount type</label>
                    <select name="type" required class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                        <option value="free_months" @selected(old('type') === 'free_months')>Free months</option>
                        <option value="percentage" @selected(old('type') === 'percentage')>Percentage off</option>
                        <option value="fixed_amount" @selected(old('type') === 'fixed_amount')>Fixed rand amount off</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Value</label>
                    <input type="number" step="0.01" min="0" name="value" value="{{ old('value') }}" required
                           placeholder="e.g. 3, 20, or 100" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                </div>
            </div>
            <p class="text-xs text-slate-500 -mt-2">Months for "free months", 0-100 for "percentage off", rands for "fixed amount off".</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Max redemptions</label>
                    <input type="number" min="1" name="max_redemptions" value="{{ old('max_redemptions') }}" placeholder="Unlimited" class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-500 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Expires</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Source / campaign</label>
                <input type="text" name="source" value="{{ old('source', 'founding_twenty') }}" placeholder="e.g. founding_twenty" class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-500 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-medium transition">
                    Create code
                </button>
                <a href="{{ route('admin.promo-codes.index') }}" class="text-sm text-slate-400 hover:text-slate-200">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
