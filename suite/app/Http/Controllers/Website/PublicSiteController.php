<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantBranding;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
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

        return view($template->blade_view, compact('tenant', 'branding', 'template'));
    }
}
