<x-app-layout>
    <x-slot name="header">Add Contractor</x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('contractors.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Contractor Details</h3>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Trade</label>
                        <input type="text" name="trade" value="{{ old('trade') }}" placeholder="e.g. Plumbing, Electrical"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        <p class="text-xs text-slate-500 mt-1">Needed to grant portal access later.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Notes</h3>
                <textarea name="notes" rows="3"
                          class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('contractors.index') }}"
                   class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-semibold">
                    Save Contractor
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
