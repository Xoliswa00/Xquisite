<x-app-layout>
    <x-slot name="header">Add a business</x-slot>

    <div class="max-w-2xl space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#D4AF37]">Add a business</h2>
            <p class="text-slate-400 text-sm mt-1">For a business you approached yourself. People who fill in a form are the organised ones, so this is how you make sure the 20 also include businesses that are really struggling. They skip the questionnaire and go straight into the review list.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 rounded-lg p-4 text-sm">
                <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.founding-twenty.store-direct') }}" class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="business_name" class="block text-xs font-medium text-slate-400 mb-1">Business name *</label>
                    <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" required class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                </div>
                <div>
                    <label for="owner_name" class="block text-xs font-medium text-slate-400 mb-1">Owner name *</label>
                    <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-400 mb-1">WhatsApp number *</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                </div>
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-400 mb-1">Email (optional)</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                </div>
                <div>
                    <label for="business_type" class="block text-xs font-medium text-slate-400 mb-1">Type of business</label>
                    <select id="business_type" name="business_type" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                        <option value="">Not sure</option>
                        @foreach(['salon' => 'Salon', 'beauty' => 'Beauty', 'wellness' => 'Wellness or spa', 'fitness' => 'Fitness', 'service' => 'Other service business', 'other' => 'Other'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('business_type') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="admin_notes" class="block text-xs font-medium text-slate-400 mb-1">Why them? What did you see?</label>
                <textarea id="admin_notes" name="admin_notes" rows="3" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">{{ old('admin_notes') }}</textarea>
            </div>
            <label class="flex items-start gap-2 text-sm text-slate-300">
                <input type="checkbox" name="consent_confirmed" value="1" required @checked(old('consent_confirmed')) class="mt-0.5 rounded bg-slate-900 border-slate-600 text-[#0078D4]">
                <span>They agreed, in person or on WhatsApp, to us keeping their details and contacting them about the programme.</span>
            </label>
            <button type="submit" class="bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg px-5 py-2.5 text-sm font-medium transition">Add to the review list</button>
        </form>
    </div>
</x-app-layout>
