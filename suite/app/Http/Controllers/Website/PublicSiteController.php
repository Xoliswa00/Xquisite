<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\TenantBranding;
use App\Services\Tenant\TenantContext;
use App\Services\Website\TenantSectionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    /**
     * Sample-data render of a catalog template, used for the live preview
     * thumbnails/iframes in the admin catalog, tenant catalog, and welcome
     * page — not tied to any real tenant or persisted anywhere.
     */
    public function preview(string $key): View
    {
        $template = Template::where('key', $key)->firstOrFail();

        $demoContent = [
            'grandure-coming-soon' => ['name' => 'Grandure Studio', 'description' => 'We are putting the finishing touches on something special.'],
            'eat-restaurant'       => ['name' => 'Eat & Chat', 'description' => 'Fresh ingredients, honest cooking, and a warm room to enjoy it in.'],
            'add-life-fitness'     => ['name' => 'Add Life Fitness', 'description' => 'A friendly neighbourhood fitness studio built around real results.'],
            'aroma-beauty-spa'     => ['name' => 'Aroma Beauty & Spa', 'description' => 'A calm space to slow down and be looked after.'],
            'beauty-salon'         => ['name' => 'Beauty Salon', 'description' => 'Cut, colour, and style — crafted around what actually suits you.'],
            'lovely-wedding'       => ['name' => 'Lovely Weddings & Events', 'description' => "We plan the day you've been dreaming of, down to the last detail."],
        ][$template->key] ?? ['name' => 'Sample Business', 'description' => 'A short description of your business goes here.'];

        $tenant = new Tenant([
            'name'    => $demoContent['name'],
            'slug'    => 'demo',
            'email'   => 'hello@example.com',
            'phone'   => '021 123 4567',
            'address' => '123 Main Street, Cape Town',
        ]);

        $branding = new TenantBranding([
            'description'  => $demoContent['description'],
            'primary_color'   => '#0078D4',
            'secondary_color' => '#002B5B',
            'accent_color'    => '#D4AF37',
            'heading_font'    => 'inter',
            'body_font'       => 'inter',
            'socials'         => ['facebook' => '#', 'instagram' => '#'],
        ]);
        $branding->setRelation('tenant', $tenant);

        $sections = $this->resolveSections(null, $template);
        $editing = false;

        return view($template->blade_view, compact('tenant', 'branding', 'template', 'sections', 'editing'));
    }

    /**
     * The app's own root route. Behavior is unchanged for every request
     * except a guest visiting a tenant's subdomain/custom domain, who now
     * gets that tenant's public website instead of the platform's welcome page.
     */
    public function root(Request $request): RedirectResponse|View
    {
        if ($request->user() !== null) {
            return redirect()->route('dashboard');
        }

        $host = $request->getHost();
        $appDomain = config('app.domain', 'xquisite.co.za');
        $isTenantHost = $host !== $appDomain && $host !== 'www.' . $appDomain;

        if ($isTenantHost && TenantContext::hasTenant()) {
            $tenant = Tenant::find(TenantContext::get());

            if ($tenant && $tenant->is_active) {
                return $this->render($tenant);
            }
        }

        return view('welcome');
    }

    /**
     * Preview the authenticated tenant's own in-progress branding against
     * their chosen (not-yet-active) template — unlike preview(), this uses
     * the tenant's REAL data, not demo content, so the Website Setup Wizard's
     * Preview step actually shows what they've built so far.
     */
    public function previewOwn(Request $request): View
    {
        return $this->renderOwn($request, editing: false);
    }

    /**
     * Same as previewOwn(), but flags the render as editing — used only by
     * the visual editor's live-preview iframe, which needs the per-section
     * hover toolbar and hidden-section indicators that public rendering
     * must never show.
     */
    public function editorPreview(Request $request): View
    {
        return $this->renderOwn($request, editing: true);
    }

    private function renderOwn(Request $request, bool $editing): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $templateKey = $tenant->preferred_template_key ?? $tenant->activeTemplate?->template_key;
        abort_unless($templateKey, 404, 'Choose a template first.');

        $template = Template::where('key', $templateKey)->firstOrFail();
        $branding = $tenant->branding ?? new TenantBranding(['tenant_id' => $tenant->id]);
        $branding->setRelation('tenant', $tenant);

        $sections = $this->resolveSections($tenant, $template);

        return view($template->blade_view, compact('tenant', 'branding', 'template', 'sections', 'editing'));
    }

    public function show(string $slug): View
    {
        $tenant = Tenant::where('slug', strtolower($slug))->where('is_active', true)->firstOrFail();

        return $this->render($tenant);
    }

    private function render(Tenant $tenant): View
    {
        $tenantTemplate = $tenant->activeTemplate;

        if (! $tenantTemplate) {
            return view('site.not-configured', compact('tenant'));
        }

        $template = $tenantTemplate->template;
        $branding = $tenant->branding ?? new TenantBranding(['tenant_id' => $tenant->id]);

        SiteVisit::record($tenant, request()->path());

        $sections = $this->resolveSections($tenant, $template);
        $editing = false;

        return view($template->blade_view, compact('tenant', 'branding', 'template', 'sections', 'editing'));
    }

    /**
     * Resolves the ordered, page-builder sections for a render. A real
     * persisted tenant gets seeded (idempotently) from their template's
     * preset the first time they have none, then their real rows. A
     * placeholder template (Coming Soon) or a not-yet-persisted demo tenant
     * (the marketplace's fake-tenant preview) never touches the database —
     * the former has no preset at all, the latter gets non-persisted,
     * in-memory rows built straight from the preset.
     */
    private function resolveSections(?Tenant $tenant, Template $template): Collection
    {
        if ($template->isPlaceholder()) {
            return collect();
        }

        if ($tenant && $tenant->exists) {
            TenantSectionSeeder::seedForTenant($tenant, $template);

            return $tenant->pageSections()->ordered()->get();
        }

        return TenantSectionSeeder::virtualSectionsFor($template);
    }
}
