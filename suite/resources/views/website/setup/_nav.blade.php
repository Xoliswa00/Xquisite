<div class="flex items-center justify-between pt-2">
    @if ($prevStep)
        <a href="{{ route('website.setup.show', ['step' => $prevStep]) }}" class="text-sm text-ink-faint hover:text-ink transition-colors">← Back</a>
    @else
        <span></span>
    @endif

    @if ($nextStep)
        <a href="{{ route('website.setup.show', ['step' => $nextStep]) }}" class="text-sm text-ink-muted hover:text-ink transition-colors">Skip for now →</a>
    @endif
</div>
