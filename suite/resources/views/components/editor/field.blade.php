@props(['field', 'value', 'namePrefix', 'dotPrefix'])

@switch($field['type'])
    @case('text')
    @case('url')
        @php
            // A handful of "text" fields (e.g. gallery images' per-photo tags)
            // store their value as an array — displayed here as a comma-separated
            // string, parsed back into an array on save (PageEditorController::update()).
            // Scoped to this case only: repeater/image values are legitimately
            // array-shaped and must never be run through implode().
            $displayValue = is_array($value) ? implode(', ', $value) : $value;
        @endphp
        <div>
            <label class="block text-xs font-medium text-ink-muted mb-1">{{ $field['label'] }}</label>
            <input type="text" name="{{ $namePrefix }}" value="{{ old($namePrefix, $displayValue) }}" maxlength="{{ $field['max'] ?? 255 }}"
                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
        </div>
        @break

    @case('textarea')
        <div>
            <label class="block text-xs font-medium text-ink-muted mb-1">{{ $field['label'] }}</label>
            <textarea name="{{ $namePrefix }}" rows="4" maxlength="{{ $field['max'] ?? 2000 }}"
                      class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old($namePrefix, $value) }}</textarea>
        </div>
        @break

    @case('select')
        <div>
            <label class="block text-xs font-medium text-ink-muted mb-1">{{ $field['label'] }}</label>
            <select name="{{ $namePrefix }}" class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                @foreach(($field['options'] ?? []) as $optKey => $optLabel)
                    <option value="{{ $optKey }}" @selected((string) old($namePrefix, $value) === (string) $optKey)>{{ $optLabel }}</option>
                @endforeach
            </select>
        </div>
        @break

    @case('image')
        <x-editor.field-image :field="$field" :value="$value" :name-prefix="$namePrefix" :dot-prefix="$dotPrefix" />
        @break

    @case('repeater')
        <x-editor.field-repeater :field="$field" :items="$value ?? []" :name-prefix="$namePrefix" :dot-prefix="$dotPrefix" />
        @break
@endswitch
