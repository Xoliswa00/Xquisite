<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\TenantBranding;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'coming-soon'    => ['name' => 'Grandure Studio', 'description' => 'We are putting the finishing touches on something special.'],
            'restaurant'     => ['name' => 'Eat & Chat', 'description' => 'Fresh ingredients, honest cooking, and a warm room to enjoy it in.'],
            'fitness'        => ['name' => 'Add Life Fitness', 'description' => 'A friendly neighbourhood fitness studio built around real results.'],
            'beauty-spa'     => ['name' => 'Aroma Beauty & Spa', 'description' => 'A calm space to slow down and be looked after.'],
            'wedding-events' => ['name' => 'Lovely Weddings & Events', 'description' => "We plan the day you've been dreaming of, down to the last detail."],
        ][$template->category] ?? ['name' => 'Sample Business', 'description' => 'A short description of your business goes here.'];

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

        return view($template->blade_view, compact('tenant', 'branding', 'template'));
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

        return view($template->blade_view, compact('tenant', 'branding', 'template'));
    }
}
