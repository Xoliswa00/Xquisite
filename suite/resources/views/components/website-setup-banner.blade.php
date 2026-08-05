@php
    $wizardTenant = auth()->user()?->tenant;
    $wizardProgress = $wizardTenant ? new \App\Services\Website\SetupWizardProgress($wizardTenant) : null;
@endphp
@if ($wizardProgress && ! $wizardProgress->isComplete())
    <div class="bg-panel-2 rounded-xl border border-[#0078D4]/20 shadow-lg shadow-[#0078D4]/8 overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#0078D4]/15 border border-[#0078D4]/30 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink">Finish setting up your website</h3>
                    <p class="text-xs text-ink-muted mt-0.5">{{ $wizardProgress->doneCount() }}/{{ $wizardProgress->totalCount() }} steps done</p>
                </div>
            </div>
            <a href="{{ route('website.setup.show') }}" class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                Continue Setup →
            </a>
        </div>
    </div>
@endif
