<x-app-layout>
    <x-slot name="header">Add Applicant</x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('applicants.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Applicant Details</h3>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">ID Number</label>
                    <input type="text" name="id_number" value="{{ old('id_number') }}"
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Interested In</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Property</label>
                        <select name="property_id" class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            <option value="">Not specified</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" @selected(old('property_id', $preUnit?->property_id) == $property->id)>{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Unit</label>
                        @if($preUnit)
                            <input type="hidden" name="unit_id" value="{{ $preUnit->id }}">
                            <input type="text" disabled value="Unit {{ $preUnit->unit_number }}"
                                   class="w-full bg-slate-800 border-slate-600 text-slate-400 rounded-lg text-sm px-3 py-2">
                        @else
                            <input type="text" disabled value="Select a unit from its page to pre-fill"
                                   class="w-full bg-slate-800 border-slate-600 text-slate-500 rounded-lg text-sm px-3 py-2">
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Affordability</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Employer</label>
                        <input type="text" name="employer" value="{{ old('employer') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Employment Type</label>
                        <select name="employment_type" class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            <option value="">Not specified</option>
                            @foreach(['permanent','contract','self_employed','unemployed','other'] as $t)
                                <option value="{{ $t }}" @selected(old('employment_type') === $t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Months Employed</label>
                        <input type="number" name="employment_months" value="{{ old('employment_months') }}" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Number of Occupants</label>
                        <input type="number" name="number_of_occupants" value="{{ old('number_of_occupants') }}" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Monthly Income (R)</label>
                        <input type="number" name="monthly_income" value="{{ old('monthly_income') }}" step="0.01" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Monthly Expenses / Debt (R)</label>
                        <input type="number" name="monthly_expenses" value="{{ old('monthly_expenses') }}" step="0.01" min="0"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                        <p class="text-xs text-slate-500 mt-1">Existing debt / recurring obligations, excluding rent.</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Rental History</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Previous Landlord Name</label>
                        <input type="text" name="previous_landlord_name" value="{{ old('previous_landlord_name') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Previous Landlord Phone</label>
                        <input type="text" name="previous_landlord_phone" value="{{ old('previous_landlord_phone') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('applicants.index') }}"
                   class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-semibold">
                    Save Applicant
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
