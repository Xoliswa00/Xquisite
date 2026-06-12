<x-app-layout>
<div class="max-w-5xl mx-auto py-10 px-4 pb-20">

    {{-- BREADCRUMBS & TITLE --}}
    <div class="mb-8">
        <nav class="flex mb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
            <a href="{{ route('products.index') }}" class="hover:text-brand-gold transition-colors">Catalog</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900">Provisioning</span>
        </nav>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Initialize Product</h2>
    </div>

    {{-- ERROR HANDLING --}}
    @if ($errors->any())
        <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <span class="font-bold text-red-800 text-sm">Validation Error Detected</span>
            </div>
            <ul class="text-xs text-red-700 space-y-1 ml-7 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: CORE INFO --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- SECTION: GENERAL INFO --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="bg-slate-50/50 px-6 py-3 border-b border-slate-200">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Identity & Classification</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-slate-400 px-1">System Group</label>
                                <select id="group-select" class="w-full border-slate-200 focus:border-brand-gold focus:ring-brand-gold rounded-xl text-sm transition-all bg-slate-50/30">
                                    <option value="">Select Group</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('product_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="product_group_id" id="group-hidden" value="{{ old('product_group_id') }}">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-slate-400 px-1">Sub-Category</label>
                                <select name="product_category_id" id="category-select" class="w-full border-slate-200 focus:border-brand-gold focus:ring-brand-gold rounded-xl text-sm transition-all bg-slate-50/30">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase text-slate-400 px-1">Display Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Enterprise Cloud POS" 
                                   class="w-full border-slate-200 focus:border-brand-gold focus:ring-brand-gold rounded-xl text-sm font-medium">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase text-slate-400 px-1">Description / Infrastructure Specs</label>
                            <textarea name="description" rows="4" placeholder="Brief system overview..."
                                      class="w-full border-slate-200 focus:border-brand-gold focus:ring-brand-gold rounded-xl text-sm">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SECTION: BOOKING / SERVICE CATEGORY --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="bg-slate-50/50 px-6 py-3 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Booking Configuration</h3>
                        <a href="{{ route('service-categories.create') }}" target="_blank"
                           class="text-[10px] font-black text-amber-500 uppercase tracking-widest hover:text-slate-900 transition-colors">
                            + New Category
                        </a>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-slate-400 px-1">Service Category</label>
                                <select name="service_category_id"
                                        class="w-full border-slate-200 focus:border-amber-400 focus:ring-amber-400 rounded-xl text-sm bg-slate-50/30">
                                    <option value="">— Uncategorized —</option>
                                    @foreach($serviceCategories as $cat)
                                        <option value="{{ $cat->id }}"
                                                {{ old('service_category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->icon ? $cat->icon . ' ' : '' }}{{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($serviceCategories->isEmpty())
                                    <p class="text-[10px] text-amber-500 px-1 mt-1">No categories yet —
                                        <a href="{{ route('service-categories.create') }}" class="underline font-bold">create one</a>.
                                    </p>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-slate-400 px-1">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" min="1" max="480"
                                       value="{{ old('duration_minutes') }}"
                                       placeholder="e.g. 60"
                                       class="w-full border-slate-200 focus:border-amber-400 focus:ring-amber-400 rounded-xl text-sm">
                                <p class="text-[10px] text-slate-400 px-1">Shown to clients on the booking menu</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION: ITEMS / ADD-ONS --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="bg-slate-50/50 px-6 py-3 border-b border-slate-200 flex justify-between items-center">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Modular Components</h3>
                        <button type="button" onclick="addItem()" class="text-[10px] font-black text-brand-gold uppercase tracking-widest hover:text-slate-900 transition-colors">+ Add Module</button>
                    </div>
                    <div id="items-wrapper" class="p-6 space-y-4">
                        {{-- Items injected via JS --}}
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: PRICING & DEPLOYMENT --}}
            <div class="space-y-6">
                
                {{-- SECTION: BILLING STRATEGY --}}
                <div class="bg-slate-900 rounded-2xl p-6 shadow-xl text-white">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 border-b border-white/10 pb-2">Financial Engine</h3>
                    
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Revenue Model</label>
                            <select name="billing_type" id="billing_type" class="w-full bg-slate-800 border-white/10 rounded-xl text-sm focus:ring-brand-gold">
                                <option value="once_off" {{ old('billing_type') == 'once_off' ? 'selected' : '' }}>Once-off Payment</option>
                                <option value="recurring" {{ old('billing_type') == 'recurring' ? 'selected' : '' }}>Recurring Subscription</option>
                            </select>
                        </div>

                        <div id="billing_cycle_div" class="space-y-2 hidden">
                            <label class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Billing Interval</label>
                            <select name="billing_cycle" class="w-full bg-slate-800 border-white/10 rounded-xl text-sm">
                                <option value="">Select Cycle</option>
                                <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>

                        <div class="space-y-4 pt-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Pricing Structure</label>
                                <select name="pricing_type" id="pricing_type" class="w-full bg-slate-800 border-white/10 rounded-xl text-sm">
                                    <option value="fixed">Fixed Rate</option>
                                    <option value="hourly">Hourly Rate</option>
                                    <option value="range">Price Range</option>
                                    <option value="custom">Quoted / Custom</option>
                                </select>
                            </div>

                            <div id="price_fields" class="space-y-3">
                                <div class="relative" id="field_price">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-bold">R</span>
                                    <input type="number" step="0.01" name="price" value="{{ old('price') }}" placeholder="0.00" 
                                           class="w-full bg-slate-800 border-white/10 rounded-xl pl-8 text-lg font-bold">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2" id="field_range" style="display:none;">
                                    <input type="number" step="0.01" name="min_price" placeholder="Min" class="bg-slate-800 border-white/10 rounded-xl text-sm">
                                    <input type="number" step="0.01" name="max_price" placeholder="Max" class="bg-slate-800 border-white/10 rounded-xl text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SAVE ACTION --}}
                <button class="w-full bg-brand-gold hover:bg-white text-slate-900 font-black uppercase tracking-widest py-4 rounded-2xl transition-all shadow-lg shadow-brand-gold/10 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3"/></svg>
                    Commit Product
                </button>
                <a href="{{ route('products.index') }}" class="block text-center text-[10px] font-bold uppercase text-slate-400 hover:text-slate-600 tracking-widest">Cancel Deployment</a>
            </div>
        </div>
    </form>
</div>

{{-- JS - REFACTORED FOR THE NEW UI --}}
<script>
const groups = @json($groups);

/* GROUP → CATEGORY */


document.addEventListener('DOMContentLoaded', function () {

    const groupSelect = document.getElementById('group-select');
    const categorySelect = document.getElementById('category-select');
    const groupHidden = document.getElementById('group-hidden');

    const oldGroup = groupHidden.value;
    const oldCategory = "{{ old('category_id') }}"; // Blade inject

    function loadCategories(groupId, selectedCategory = null) {
        categorySelect.innerHTML = '<option value="">Select Category</option>';

        let selectedGroup = groups.find(g => g.id == groupId);

        if (selectedGroup) {
            selectedGroup.categories.forEach(cat => {
                let option = document.createElement('option');
                option.value = cat.id;
                option.text = cat.name;

                if (selectedCategory && selectedCategory == cat.id) {
                    option.selected = true;
                }

                categorySelect.appendChild(option);
            });
        }
    }

    // On change
    groupSelect.addEventListener('change', function () {
        let groupId = this.value;
        groupHidden.value = groupId;
        loadCategories(groupId);
    });

    // 🔥 On reload (important part)
    if (oldGroup) {
        groupSelect.value = oldGroup;
        loadCategories(oldGroup, oldCategory);
    }

});
/* BILLING TYPE TOGGLE */
document.getElementById('billing_type').addEventListener('change', function () {
    document.getElementById('billing_cycle_div').classList.toggle('hidden', this.value !== 'recurring');
});

/* PRICING TYPE DYNAMICS */
document.getElementById('pricing_type').addEventListener('change', function () {
    let type = this.value;
    document.getElementById('field_price').style.display = (type === 'fixed' || type === 'hourly') ? 'block' : 'none';
    document.getElementById('field_range').style.display = (type === 'range') ? 'grid' : 'none';
});

/* ITEMS ENGINE */
let itemIndex = {{ old('items') ? count(old('items')) : 0 }};
function addItem() {
    let wrapper = document.getElementById('items-wrapper');
    let html = `
        <div class="border border-slate-100 p-4 rounded-xl bg-slate-50/50 group relative">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input type="text" name="items[${itemIndex}][name]" placeholder="Module Name" class="border-slate-200 rounded-lg text-sm">
                <input type="number" step="0.01" name="items[${itemIndex}][price]" placeholder="Add-on Price (R)" class="border-slate-200 rounded-lg text-sm">
                <input type="text" name="items[${itemIndex}][description]" placeholder="Short description" class="md:col-span-2 border-slate-200 rounded-lg text-sm">
            </div>
            <div class="mt-3 flex items-center gap-2">
                <input type="checkbox" name="items[${itemIndex}][is_included]" class="rounded text-brand-gold focus:ring-brand-gold" checked>
                <label class="text-[10px] font-black uppercase text-slate-400">Included in Base License</label>
            </div>
        </div>`;
    wrapper.insertAdjacentHTML('beforeend', html);
    itemIndex++;
}

{{-- REHYDRATE UI --}}
window.onload = function() {
    if ("{{ old('billing_type') }}" === 'recurring') document.getElementById('billing_cycle_div').classList.remove('hidden');
    document.getElementById('pricing_type').dispatchEvent(new Event('change'));
    @if(!old('items')) addItem(); @endif
};
</script>
</x-app-layout>