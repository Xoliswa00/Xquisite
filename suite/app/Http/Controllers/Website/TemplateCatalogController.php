<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $templates = Template::visible()->active()->ordered()->get();
        $activeTemplate = $tenant->activeTemplate?->template;
        $categories = $templates->pluck('category')->unique()->filter()->values();

        return view('website.templates.index', compact('tenant', 'templates', 'activeTemplate', 'categories'));
    }

    public function show(Request $request, Template $template): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless($template->is_visible && $template->is_active, 404);

        $activeTemplateKey = $tenant->activeTemplate?->template_key;

        return view('website.templates.show', compact('template', 'activeTemplateKey'));
    }

    public function activate(Request $request, Template $template): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless($template->is_active && $template->is_visible, 404);
        abort_unless($template->isFree(), 403, 'Paid templates are coming soon.');

        $hadTemplateBefore = $tenant->activeTemplate()->exists();

        $tenant->activateTemplate($template->key, $request->user()->id);

        if (! $tenant->branding) {
            $tenant->branding()->create([]);
        }

        if (! $hadTemplateBefore) {
            return redirect()->route('website.branding.edit')
                ->with('success', "{$template->name} is now live. Let's add your branding.");
        }

        return redirect()->route('website.templates.index')
            ->with('success', "Switched to {$template->name}. Your branding carried over.");
    }
}
