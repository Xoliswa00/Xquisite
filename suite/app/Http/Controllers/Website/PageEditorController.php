<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\TenantPageSection;
use App\Services\Website\SectionTypeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PageEditorController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $sections = $tenant->pageSections()->ordered()->get();
        $registry = SectionTypeRegistry::all();

        return view('website.editor.show', compact('tenant', 'sections', 'registry'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $validated = $request->validate([
            'type' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! SectionTypeRegistry::exists($value)) {
                    $fail('That section type does not exist.');
                }
            }],
        ]);

        $nextOrder = (int) $tenant->pageSections()->max('sort_order') + 1;

        TenantPageSection::create([
            'tenant_id'  => $tenant->id,
            'type'       => $validated['type'],
            'variant'    => SectionTypeRegistry::defaultVariantFor($validated['type']),
            'content'    => SectionTypeRegistry::defaultContentFor($validated['type']),
            'sort_order' => $nextOrder,
            'is_visible' => true,
        ]);

        return redirect()->route('website.editor.edit')->with('success', 'Section added.');
    }

    public function update(Request $request, TenantPageSection $section): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        $rules = SectionTypeRegistry::validationRulesFor($section->type);
        $rules['variant'] = 'nullable|string';
        $rules['add_item'] = 'nullable|string';
        $rules['_image_uploads'] = 'nullable|array';
        $rules['_image_uploads.*'] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096';

        $validated = $request->validate($rules);

        $content = $this->stripDeletedRepeaterRows($validated['content'] ?? []);
        $content = $this->parseTagsFields($content);
        $content = $this->applyImageUploads($request, $section, $content);

        if ($addTo = $validated['add_item'] ?? null) {
            $field = SectionTypeRegistry::fieldDefinitionAtPath($section->type, $addTo);

            if ($field && ($field['type'] ?? null) === 'repeater') {
                $rows = data_get($content, $addTo, []);
                $rows[] = SectionTypeRegistry::blankRepeaterRow($field);
                data_set($content, $addTo, $rows);
            }
        }

        $section->update([
            'variant' => $validated['variant'] ?? $section->variant,
            'content' => $content,
        ]);

        return redirect()->route('website.editor.edit')->with('success', $section->label() . ' updated.');
    }

    /**
     * Gallery images' "tags" field is stored as a string[] (the Alpine
     * lightbox filter does `item.tags.includes(filter)`) but edited as a
     * single comma-separated text input — split it back into an array here.
     */
    private function parseTagsFields(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            if ($key === 'tags' && is_string($item)) {
                $value[$key] = collect(explode(',', $item))->map(fn ($t) => trim($t))->filter()->values()->all();
                continue;
            }

            $value[$key] = $this->parseTagsFields($item);
        }

        return $value;
    }

    /**
     * Image fields upload as part of the same Save submission (one form,
     * one button) rather than a separate per-field endpoint — a nested
     * &lt;form&gt; per image inside the main content form isn't valid HTML.
     * Each uploaded file is keyed by its dot-path into content
     * (e.g. "image_url" or the nested "items.2.photo_url").
     */
    private function applyImageUploads(Request $request, TenantPageSection $section, array $content): array
    {
        foreach ($request->file('_image_uploads', []) as $dotPath => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store("public/branding/{$section->tenant_id}/sections");
            data_set($content, $dotPath, Storage::url($path));
        }

        return $content;
    }

    /**
     * Repeater rows carry a synthetic "_delete" checkbox (not part of the
     * registry schema) so removing a row needs no JS — walks the whole
     * content tree and drops any array element that has one checked.
     */
    private function stripDeletedRepeaterRows(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $isList = array_is_list($value);

        if ($isList && collect($value)->contains(fn ($v) => is_array($v) && array_key_exists('_delete', $v))) {
            $value = collect($value)
                ->reject(fn ($row) => is_array($row) && ! empty($row['_delete']))
                ->map(function ($row) {
                    if (is_array($row)) {
                        unset($row['_delete']);
                    }

                    return $row;
                })
                ->values()
                ->all();
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->stripDeletedRepeaterRows($item);
        }

        return $value;
    }

    public function toggleVisibility(Request $request, TenantPageSection $section): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        $section->update(['is_visible' => ! $section->is_visible]);

        return redirect()->route('website.editor.edit')
            ->with('success', $section->label() . ($section->is_visible ? ' shown.' : ' hidden.'));
    }

    public function duplicate(Request $request, TenantPageSection $section): RedirectResponse
    {
        $tenant = $this->authorizeSection($request, $section);

        DB::transaction(function () use ($tenant, $section) {
            $tenant->pageSections()
                ->where('sort_order', '>', $section->sort_order)
                ->increment('sort_order');

            TenantPageSection::create([
                'tenant_id'  => $tenant->id,
                'type'       => $section->type,
                'variant'    => $section->variant,
                'content'    => $section->content,
                'sort_order' => $section->sort_order + 1,
                'is_visible' => $section->is_visible,
            ]);
        });

        return redirect()->route('website.editor.edit')->with('success', $section->label() . ' duplicated.');
    }

    public function destroy(Request $request, TenantPageSection $section): RedirectResponse
    {
        $tenant = $this->authorizeSection($request, $section);
        $label = $section->label();

        DB::transaction(function () use ($tenant, $section) {
            $section->delete();

            $tenant->pageSections()->ordered()->get()->values()
                ->each(fn ($s, $i) => $s->sort_order === $i ? null : $s->update(['sort_order' => $i]));
        });

        return redirect()->route('website.editor.edit')->with('success', $label . ' deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        $ownedIds = $tenant->pageSections()->pluck('id')->all();

        DB::transaction(function () use ($validated, $ownedIds) {
            foreach ($validated['order'] as $index => $sectionId) {
                if (in_array($sectionId, $ownedIds, true)) {
                    TenantPageSection::where('id', $sectionId)->update(['sort_order' => $index]);
                }
            }
        });

        // Called via fetch from the drag-and-drop handler (the one mutation
        // in this controller that fires without an explicit form submit) —
        // JSON in, JSON out, unlike every other action here which redirects.
        return response()->json(['success' => true]);
    }

    private function authorizeSection(Request $request, TenantPageSection $section)
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless($section->tenant_id === $tenant->id, 403);

        return $tenant;
    }
}
