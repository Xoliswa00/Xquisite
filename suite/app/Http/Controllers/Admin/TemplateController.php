<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformModule;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::ordered()->get()->groupBy(fn (Template $t) => $t->category ?: 'uncategorized');

        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        $modules = PlatformModule::ordered()->get();

        return view('admin.templates.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, isCreate: true);
        $data = $this->applyChangelogEntry($request, $data, []);

        $data['is_active']   = $request->boolean('is_active', true);
        $data['is_visible']  = $request->boolean('is_visible', true);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order']  = $data['sort_order'] ?? 0;
        $data['modules_supported'] = $request->input('modules_supported', []);

        Template::create($data);

        return redirect()->route('admin.templates.index')
            ->with('success', "{$data['name']} added to the template catalog.");
    }

    public function edit(Template $template)
    {
        $modules = PlatformModule::ordered()->get();

        return view('admin.templates.edit', compact('template', 'modules'));
    }

    public function update(Request $request, Template $template)
    {
        $data = $this->validated($request, isCreate: false);
        $data = $this->applyChangelogEntry($request, $data, $template->changelog ?? []);

        $data['is_active']   = $request->boolean('is_active');
        $data['is_visible']  = $request->boolean('is_visible');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order']  = $data['sort_order'] ?? $template->sort_order;
        $data['modules_supported'] = $request->input('modules_supported', []);

        $template->update($data);

        return redirect()->route('admin.templates.index')
            ->with('success', "{$template->name} updated.");
    }

    public function updateStatus(Request $request, Template $template)
    {
        $data = $request->validate([
            'is_active'  => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
        ]);

        $template->update($data);

        return back()->with('success', "{$template->name} status updated.");
    }

    /**
     * Calls Google's PageSpeed Insights API against the template's live public
     * preview URL. Only works when that URL is actually reachable from the
     * public internet (i.e. a deployed environment, not localhost) — fails
     * gracefully with a flash message rather than fabricating a score.
     */
    public function refreshScores(Request $request, Template $template)
    {
        $previewUrl = route('template.preview', $template->key);

        if (str_contains($previewUrl, 'localhost') || str_contains($previewUrl, '127.0.0.1')) {
            return back()->with('error', 'Scores need a publicly reachable URL — this only works once the app is deployed, not on localhost.');
        }

        try {
            $query = [
                'url'      => $previewUrl,
                'category' => ['performance', 'seo', 'accessibility'],
                'strategy' => 'mobile',
            ];

            if ($key = config('services.pagespeed.key')) {
                $query['key'] = $key;
            }

            $response = Http::timeout(60)->get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', $query);

            if (! $response->successful()) {
                throw new \RuntimeException('PageSpeed Insights returned HTTP ' . $response->status());
            }

            $categories = $response->json('lighthouseResult.categories');

            $template->update([
                'speed_score'         => isset($categories['performance']['score']) ? (int) round($categories['performance']['score'] * 100) : $template->speed_score,
                'seo_score'           => isset($categories['seo']['score']) ? (int) round($categories['seo']['score'] * 100) : $template->seo_score,
                'accessibility_score' => isset($categories['accessibility']['score']) ? (int) round($categories['accessibility']['score'] * 100) : $template->accessibility_score,
                'scores_checked_at'   => now(),
            ]);

            return back()->with('success', "Scores refreshed for {$template->name}.");
        } catch (\Throwable $e) {
            Log::warning('PageSpeed score refresh failed', ['template' => $template->key, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not reach PageSpeed Insights — you can enter scores manually below instead.');
        }
    }

    private function validated(Request $request, bool $isCreate): array
    {
        $rules = [
            'name'               => 'required|string|max:100',
            'description'        => 'nullable|string|max:1000',
            'category'           => 'nullable|string|max:50',
            'preview_image_url'  => 'nullable|string|max:255',
            'blade_view'         => ['required', 'string', 'max:150', function ($attribute, $value, $fail) {
                if (! View::exists($value)) {
                    $fail("The blade view [{$value}] does not exist.");
                }
            }],
            'version'            => 'nullable|string|max:20',
            'author'             => 'nullable|string|max:100',
            'supports_theme_toggle' => 'boolean',
            'speed_score'         => 'nullable|integer|min:0|max:100',
            'seo_score'           => 'nullable|integer|min:0|max:100',
            'accessibility_score' => 'nullable|integer|min:0|max:100',
            'rating_override'     => 'nullable|numeric|min:0|max:5',
            'price_type'         => 'required|in:free,one_time,monthly,annual',
            'price'              => 'nullable|required_unless:price_type,free|numeric|min:0',
            'default_primary_color'   => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'default_secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'default_accent_color'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'boolean',
            'is_visible'         => 'boolean',
            'is_featured'        => 'boolean',
            'release_notes'      => 'nullable|string|max:500',
            'modules_supported'  => 'nullable|array',
            'modules_supported.*' => 'string',
        ];

        if ($isCreate) {
            $rules['key'] = ['required', 'string', 'alpha_dash', Rule::unique('templates', 'key')];
        }

        return $request->validate($rules);
    }

    /**
     * "Release notes" is a plain textarea, not a repeater — each save with
     * non-empty notes prepends one {version, date, notes} entry to the
     * template's changelog JSON column, newest first.
     */
    private function applyChangelogEntry(Request $request, array $data, array $existingChangelog): array
    {
        $notes = $data['release_notes'] ?? null;
        unset($data['release_notes']);

        if (! $notes) {
            return $data;
        }

        array_unshift($existingChangelog, [
            'version' => $data['version'] ?? '1.0.0',
            'date'    => now()->toDateString(),
            'notes'   => $notes,
        ]);

        $data['changelog'] = $existingChangelog;

        return $data;
    }
}
