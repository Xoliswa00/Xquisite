<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6" x-data="{ industry: '{{ old('industry', $tenant->industry ?? '') }}' }">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if ($errors->any())
            <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
                @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
            </div>
        @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-ink">What kind of business is this?</h2>
                <p class="text-sm text-ink-muted mt-1">This helps us suggest the right template for you.</p>
            </div>

            <form method="POST" action="{{ route('website.setup.business-type') }}" class="space-y-4">
                @csrf
                <select name="industry" x-model="industry" required
                        class="w-full bg-panel border border-line-2 text-ink text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                    <option value="">— Select your industry —</option>
                    <option value="salon">Hair Salon</option>
                    <option value="spa">Spa &amp; Wellness</option>
                    <option value="barbershop">Barbershop</option>
                    <option value="beauty">Beauty Studio</option>
                    <option value="nail">Nail Salon</option>
                    <option value="massage">Massage Therapy</option>
                    <option value="fitness">Gym &amp; Fitness</option>
                    <option value="hospitality">Restaurant &amp; Hospitality</option>
                    <option value="events">Wedding &amp; Event Planning</option>
                    <option value="technology">Technology</option>
                    <option value="retail">Retail</option>
                    <option value="property">Property Management</option>
                    <option value="coming_soon">Not ready yet — just need a Coming Soon page</option>
                    <option value="other">Other</option>
                </select>

                <div x-show="industry === 'other'" x-cloak>
                    <input type="text" name="industry_other" placeholder="Please specify your industry"
                           class="w-full bg-panel border border-line-2 text-ink text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>

                <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                    Continue
                </button>
            </form>
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
