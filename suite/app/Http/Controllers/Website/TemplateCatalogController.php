<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplatePurchase;
use App\Models\TemplateReview;
use App\Models\TenantTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateCatalogController extends Controller
{
    private const SORTS = ['featured', 'newest', 'popular', 'price_low', 'price_high', 'rating', 'updated'];

    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'featured';
        $price = in_array($request->query('price'), ['free', 'premium'], true) ? $request->query('price') : null;
        $category = $request->query('category');
        $darkModeOnly = $request->boolean('dark_mode');

        $query = Template::visible()->active();

        if ($price === 'free') {
            $query->free();
        } elseif ($price === 'premium') {
            $query->premium();
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($darkModeOnly) {
            $query->where('supports_theme_toggle', true);
        }

        $query = match ($sort) {
            'newest'     => $query->newest(),
            'popular'    => $query->popular(),
            'price_low'  => $query->orderByRaw("price_type = 'free' desc")->orderBy('price'),
            'price_high' => $query->orderByRaw("price_type = 'free' asc")->orderByDesc('price'),
            'rating'     => $query->topRated(),
            'updated'    => $query->recentlyUpdated(),
            default      => $query->featuredFirst(),
        };

        $templates = $query->get();
        $activeTemplate = $tenant->activeTemplate?->template;
        $categories = Template::visible()->active()->pluck('category')->unique()->filter()->values();
        $purchasedKeys = TemplatePurchase::where('tenant_id', $tenant->id)->where('status', 'paid')->pluck('template_key');
        $canActivateReal = $tenant->canActivateRealTemplate();

        return view('website.templates.index', compact('tenant', 'templates', 'activeTemplate', 'categories', 'sort', 'price', 'category', 'darkModeOnly', 'purchasedKeys', 'canActivateReal'));
    }

    public function show(Request $request, Template $template): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless($template->is_visible && $template->is_active, 404);

        $activeTemplateKey = $tenant->activeTemplate?->template_key;
        $hasPurchased = $template->isPremium() && TemplatePurchase::where('tenant_id', $tenant->id)
            ->where('template_key', $template->key)->where('status', 'paid')->exists();
        $canReview = TenantTemplate::where('tenant_id', $tenant->id)
            ->where('template_key', $template->key)->exists();
        $myReview = TemplateReview::where('tenant_id', $tenant->id)
            ->where('template_key', $template->key)->first();
        $reviews = $template->reviews()->approved()->latest()->with('tenant')->take(20)->get();
        $canActivateReal = $tenant->canActivateRealTemplate();
        $isPreferredPick = $tenant->preferred_template_key === $template->key;

        return view('website.templates.show', compact('template', 'activeTemplateKey', 'hasPurchased', 'canReview', 'myReview', 'reviews', 'canActivateReal', 'isPreferredPick'));
    }

    public function activate(Request $request, Template $template): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless($template->is_active && $template->is_visible, 404);

        if (! $template->isPlaceholder() && ! $tenant->canActivateRealTemplate()) {
            $tenant->update(['preferred_template_key' => $template->key]);

            $target = $request->boolean('from_wizard')
                ? redirect()->route('website.setup.show')
                : redirect()->route('website.templates.index');

            return $target->with('info',
                "{$template->name} is saved as your pick — it unlocks the moment you're off your free trial. " .
                'Your site stays on the free Coming Soon page until then.'
            );
        }

        if ($template->isPremium()) {
            $hasPurchased = TemplatePurchase::where('tenant_id', $tenant->id)
                ->where('template_key', $template->key)->where('status', 'paid')->exists();

            abort_unless($hasPurchased, 403, 'Purchase this template before activating it.');
        }

        $hadTemplateBefore = $tenant->activeTemplate()->exists();

        $tenant->activateTemplate($template->key, $request->user()->id);

        if (! $tenant->branding) {
            $tenant->branding()->create([]);
        }

        if ($request->boolean('from_wizard')) {
            return redirect()->route('website.setup.show')
                ->with('success', "{$template->name} is now live.");
        }

        if (! $hadTemplateBefore) {
            return redirect()->route('website.branding.edit')
                ->with('success', "{$template->name} is now live. Let's add your branding.");
        }

        return redirect()->route('website.templates.index')
            ->with('success', "Switched to {$template->name}. Your branding carried over.");
    }
}
