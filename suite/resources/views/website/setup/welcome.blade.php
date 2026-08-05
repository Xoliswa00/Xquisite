<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <div class="bg-panel-2 rounded-xl border border-line p-8 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-[#0078D4]/15 border border-[#0078D4]/30 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18 15 15 0 010-18z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-ink" style="font-family:'Montserrat',sans-serif">Let's build your website</h1>
            <p class="text-sm text-ink-muted max-w-md mx-auto">
                A few quick steps — business type, template, logo, colours, and you'll have a real public website.
                Everything saves as you go, so you can stop and pick up right where you left off any time.
            </p>
            <a href="{{ route('website.setup.show', ['step' => 'business-type']) }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-xl transition-colors">
                Get Started
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>

    </div>
</x-app-layout>
