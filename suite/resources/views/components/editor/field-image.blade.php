@props(['field', 'value', 'namePrefix', 'dotPrefix'])

<div x-data="{ picked: false }">
    <label class="block text-xs font-medium text-ink-muted mb-1">{{ $field['label'] }}</label>
    <input type="hidden" name="{{ $namePrefix }}" value="{{ old($namePrefix, $value) }}">
    <div class="flex items-center gap-3">
        @if($value)
            <img src="{{ $value }}" alt="" class="h-14 w-14 rounded-lg border border-line object-cover">
        @else
            <div class="h-14 w-14 rounded-lg border border-line-2 bg-panel-2"></div>
        @endif
        <label class="cursor-pointer rounded-lg border border-line-2 px-3 py-1.5 text-xs text-ink transition-colors hover:border-ink-faint">
            <span x-show="!picked">Change</span>
            <span x-cloak x-show="picked" class="text-[#0078D4]">Selected — Save to upload</span>
            <input type="file" name="_image_uploads[{{ $dotPrefix }}]" accept="image/*" class="hidden" @change="picked = true">
        </label>
    </div>
</div>
