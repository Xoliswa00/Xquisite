@props(['section'])

@php
    $fields = \App\Services\Website\SectionTypeRegistry::fieldsFor($section->type);
    $variants = \App\Services\Website\SectionTypeRegistry::variantsFor($section->type);
@endphp

<form id="section-form-{{ $section->id }}" method="POST" action="{{ route('website.editor.sections.update', $section) }}" class="space-y-5" enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    @if(count($variants) > 1)
        <div>
            <label class="block text-xs font-medium text-ink-muted mb-1">Layout</label>
            <select name="variant" class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                @foreach($variants as $key => $label)
                    <option value="{{ $key }}" @selected($section->resolvedVariant() === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @foreach($fields as $field)
        <x-editor.field
            :field="$field"
            :value="data_get($section->content, $field['key'])"
            :name-prefix="'content[' . $field['key'] . ']'"
            :dot-prefix="$field['key']"
            :section="$section"
        />
    @endforeach

    <button type="submit" class="w-full rounded-lg bg-[#0078D4] hover:bg-[#0078D4]/90 px-4 py-2.5 text-sm font-semibold text-white transition-colors">
        Save
    </button>
</form>
