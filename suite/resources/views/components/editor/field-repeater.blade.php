@props(['field', 'items', 'namePrefix', 'dotPrefix'])

<div>
    <label class="block text-xs font-medium text-ink-muted mb-2">{{ $field['label'] }}</label>

    <div class="space-y-3">
        @forelse($items as $i => $item)
            <div class="space-y-3 rounded-lg border border-line p-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-ink-faint">#{{ $i + 1 }}</span>
                    <label class="flex items-center gap-1.5 text-xs text-red-500">
                        <input type="checkbox" name="{{ $namePrefix }}[{{ $i }}][_delete]" value="1" class="h-3.5 w-3.5 rounded border-line-2">
                        Remove
                    </label>
                </div>

                @foreach(($field['item_fields'] ?? []) as $itemField)
                    <x-editor.field
                        :field="$itemField"
                        :value="data_get($item, $itemField['key'])"
                        :name-prefix="$namePrefix . '[' . $i . '][' . $itemField['key'] . ']'"
                        :dot-prefix="$dotPrefix . '.' . $i . '.' . $itemField['key']"
                    />
                @endforeach
            </div>
        @empty
            <p class="text-xs text-ink-faint">No items yet.</p>
        @endforelse
    </div>

    <button
        type="submit" name="add_item" value="{{ $dotPrefix }}"
        class="mt-3 rounded-lg border border-dashed border-line-2 px-3 py-1.5 text-xs text-ink-muted transition-colors hover:border-[#0078D4] hover:text-[#0078D4]"
    >
        + Add {{ Str::singular($field['label']) }}
    </button>
</div>
