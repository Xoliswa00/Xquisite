<x-guest-layout>
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-xl mx-auto">

        <div class="text-center mb-8">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Xquisite Creations</p>
            <h1 class="text-2xl font-bold text-gray-900">Request a Service</h1>
            <p class="text-gray-500 mt-2 text-sm">Tell us what you need — a new website, a dashboard, automation, or ongoing support — and we'll get back to you with next steps.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm text-center">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm space-y-1">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('request-service.store') }}" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
            @csrf

            {{-- Honeypot — hidden from real visitors via CSS, bots tend to fill every field --}}
            <div style="position:absolute; left:-9999px;" aria-hidden="true">
                <label>Website</label>
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <input type="text" name="company" value="{{ old('company') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">What do you need? *</label>
                <select name="category" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                    <option value="digital_solutions" @selected(old('category', 'digital_solutions') === 'digital_solutions')>A website, online store, or booking system</option>
                    <option value="software_solutions" @selected(old('category') === 'software_solutions')>A custom business application or portal</option>
                    <option value="business_automation" @selected(old('category') === 'business_automation')>Workflow or business process automation</option>
                    <option value="data_intelligence" @selected(old('category') === 'data_intelligence')>A dashboard or reporting/analytics setup</option>
                    <option value="ongoing_support" @selected(old('category') === 'ongoing_support')>Ongoing hosting, maintenance, or support</option>
                    <option value="other" @selected(old('category') === 'other')>Something else</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tell us about it *</label>
                <textarea name="description" rows="5" required placeholder="Goals, must-haves, who it's for, anything that helps us scope this..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget range</label>
                    <input type="text" name="budget_range" value="{{ old('budget_range') }}" placeholder="e.g. R10,000 – R20,000"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timeline</label>
                    <input type="text" name="timeline" value="{{ old('timeline') }}" placeholder="e.g. Within 2 months"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm font-semibold rounded-lg">
                Send Request
            </button>
        </form>
    </div>
</div>
</x-guest-layout>
