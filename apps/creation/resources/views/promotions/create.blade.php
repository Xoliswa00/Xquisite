<x-app-layout>
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- HEADER --}}
    <div class="border-b border-slate-200 pb-8">
        <nav class="flex mb-2">
            <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                <li><a href="{{ route('promotions.index') }}" class="hover:text-slate-700">Promotions</a></li>
                <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li class="text-slate-900">{{ isset($promotion) ? 'Edit' : 'New Promotion' }}</li>
            </ol>
        </nav>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter">
            {{ isset($promotion) ? 'Edit: ' . $promotion->name : 'Create Promotion' }}
        </h2>
        <p class="text-slate-400 text-sm mt-1">Set a time-based discount with an optional promo code and usage cap.</p>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-sm text-rose-600">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ isset($promotion) ? route('promotions.update', $promotion) : route('promotions.store') }}"
          x-data="promoForm()">
        @csrf
        @if(isset($promotion)) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT: MAIN --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- BASIC INFO --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Details</h3>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Promotion Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $promotion->name ?? '') }}"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                               placeholder="e.g. Summer Sale 20%" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 resize-none"
                                  placeholder="What this promotion is for...">{{ old('description', $promotion->description ?? '') }}</textarea>
                    </div>

                    {{-- PROMO CODE --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Promo Code <span class="text-slate-300 font-normal normal-case text-xs">— optional</span></label>
                        <div class="flex gap-2">
                            <input type="text" name="code" id="promoCode"
                                   value="{{ old('code', $promotion->code ?? '') }}"
                                   x-model="code"
                                   class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-slate-900"
                                   placeholder="SUMMER20">
                            <button type="button" @click="generateCode()"
                                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors uppercase tracking-wide">
                                Generate
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Leave empty to apply the discount automatically (no code required).</p>
                    </div>
                </div>

                {{-- DISCOUNT --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Discount</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Discount Type</label>
                            <select name="discount_type" x-model="discountType"
                                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                                <option value="percentage" {{ old('discount_type', $promotion->discount_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed"      {{ old('discount_type', $promotion->discount_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount (R)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                <span x-text="discountType === 'percentage' ? 'Percentage (%)' : 'Amount (R)'">Percentage (%)</span>
                            </label>
                            <input type="number" name="discount_value" step="0.01" min="0"
                                   value="{{ old('discount_value', $promotion->discount_value ?? '') }}"
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                                   placeholder="0.00" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Applies To</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['all' => 'Everything', 'products' => 'Products Only', 'combos' => 'Combos Only'] as $val => $label)
                            <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all text-center
                                         {{ old('applies_to', $promotion->applies_to ?? 'all') === $val ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 hover:border-slate-400 bg-white text-slate-600' }}">
                                <input type="radio" name="applies_to" value="{{ $val }}"
                                       {{ old('applies_to', $promotion->applies_to ?? 'all') === $val ? 'checked' : '' }}
                                       class="sr-only">
                                <span class="text-xs font-bold">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- TIME WINDOW --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Time Window <span class="text-slate-300 font-normal normal-case text-xs">— optional</span></h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Starts At</label>
                            <input type="datetime-local" name="valid_from"
                                   value="{{ old('valid_from', isset($promotion) && $promotion->valid_from ? $promotion->valid_from->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Ends At</label>
                            <input type="datetime-local" name="valid_until"
                                   value="{{ old('valid_until', isset($promotion) && $promotion->valid_until ? $promotion->valid_until->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Max Uses <span class="text-slate-300 font-normal normal-case text-xs">— leave empty for unlimited</span></label>
                        <input type="number" name="max_uses" min="1"
                               value="{{ old('max_uses', $promotion->max_uses ?? '') }}"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                               placeholder="e.g. 100">
                    </div>
                </div>
            </div>

            {{-- RIGHT: SIDEBAR --}}
            <div class="space-y-6">

                {{-- PUBLISH --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Publish</h3>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded accent-slate-900">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Active</p>
                            <p class="text-xs text-slate-400">Promotion is live</p>
                        </div>
                    </label>
                </div>

                {{-- PREVIEW --}}
                <div class="bg-violet-50 border border-violet-200 rounded-2xl p-5 space-y-2">
                    <p class="text-[10px] font-black uppercase tracking-widest text-violet-500 mb-3">Preview</p>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <code class="text-sm font-black text-violet-700 tracking-widest" x-text="code || 'AUTO-APPLIED'"></code>
                    </div>
                    <p class="text-xs text-slate-500">
                        <span x-text="discountType === 'percentage' ? 'Percentage' : 'Fixed'"></span> discount on
                        <span class="font-bold text-slate-700">all items</span>
                    </p>
                </div>

                {{-- ACTIONS --}}
                <div class="space-y-3">
                    <button type="submit"
                            class="w-full bg-slate-900 hover:bg-violet-600 text-white font-bold py-3 rounded-xl text-sm transition-all duration-300 shadow-lg shadow-slate-200">
                        {{ isset($promotion) ? 'Update Promotion' : 'Create Promotion' }}
                    </button>
                    <a href="{{ route('promotions.index') }}"
                       class="block w-full text-center bg-white border border-slate-200 text-slate-600 hover:text-slate-900 font-semibold py-3 rounded-xl text-sm transition-all">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function promoForm() {
    return {
        discountType: '{{ old('discount_type', $promotion->discount_type ?? 'percentage') }}',
        code: '{{ old('code', $promotion->code ?? '') }}',

        generateCode() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 8; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.code = result;
        },
    };
}

// Radio button styling
document.querySelectorAll('input[name="applies_to"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="applies_to"]').forEach(r => {
            r.closest('label').className = r.closest('label').className
                .replace('border-slate-900 bg-slate-900 text-white', 'border-slate-200 hover:border-slate-400 bg-white text-slate-600');
        });
        this.closest('label').className = this.closest('label').className
            .replace('border-slate-200 hover:border-slate-400 bg-white text-slate-600', 'border-slate-900 bg-slate-900 text-white');
    });
});
</script>
</x-app-layout>
