@php
    $hasPick = ! empty($tenant->preferred_template_key) || $tenant->activeTemplate;
@endphp
<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-4xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-ink">Preview your site</h2>
                <p class="text-sm text-ink-muted mt-1">This is your actual branding — colours, logo, and content — on your chosen template.</p>
            </div>

            @if ($hasPick)
                <div class="rounded-lg overflow-hidden border border-line bg-white" style="height: 70vh;">
                    <iframe src="{{ route('website.setup.preview-own') }}" class="w-full h-full border-0" loading="lazy"></iframe>
                </div>
                <a href="{{ route('website.editor.edit') }}" class="inline-block text-sm text-[#0078D4] hover:text-[#B8D4F0]">
                    Open the full website editor →
                </a>
            @else
                <p class="text-sm text-ink-faint">Choose a template first to see a preview.</p>
                <a href="{{ route('website.setup.show', ['step' => 'template']) }}" class="inline-block text-sm text-[#0078D4] hover:text-[#B8D4F0]">Choose a template →</a>
            @endif
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
