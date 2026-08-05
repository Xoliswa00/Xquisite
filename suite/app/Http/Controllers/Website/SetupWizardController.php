<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplatePurchase;
use App\Services\Website\SetupWizardProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SetupWizardController extends Controller
{
    public function show(Request $request, ?string $step = null): View|RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $progress = new SetupWizardProgress($tenant);

        if ($step === null) {
            return redirect()->route('website.setup.show', ['step' => $progress->resumeStep()]);
        }

        abort_unless(in_array($step, SetupWizardProgress::STEPS, true), 404);

        $branding = $tenant->branding;
        $completed = $progress->completed();
        $steps = SetupWizardProgress::STEPS;
        $currentIndex = array_search($step, $steps, true);
        $nextStep = $steps[$currentIndex + 1] ?? null;
        $prevStep = $currentIndex > 0 ? $steps[$currentIndex - 1] : null;

        $data = [
            'tenant'     => $tenant,
            'branding'   => $branding,
            'step'       => $step,
            'steps'      => $steps,
            'completed'  => $completed,
            'doneCount'  => $progress->doneCount(),
            'totalCount' => $progress->totalCount(),
            'nextStep'   => $nextStep,
            'prevStep'   => $prevStep,
            'fonts'      => config('branding.fonts'),
        ];

        if ($step === 'template') {
            $data['activeTemplate'] = $tenant->activeTemplate?->template;
            $data['preferredTemplate'] = $tenant->preferred_template_key
                ? Template::where('key', $tenant->preferred_template_key)->first()
                : null;
        }

        if ($step === 'publish') {
            $publishKey = $tenant->preferred_template_key ?? $tenant->activeTemplate?->template_key;
            $publishTemplate = $publishKey ? Template::where('key', $publishKey)->first() : null;
            $alreadyLive = $tenant->activeTemplate && ! $tenant->activeTemplate->template->isPlaceholder()
                && $tenant->activeTemplate->template_key === $publishKey;

            $needsCheckout = $publishTemplate
                && $publishTemplate->isPremium()
                && $tenant->canActivateRealTemplate()
                && ! TemplatePurchase::where('tenant_id', $tenant->id)->where('template_key', $publishTemplate->key)->where('status', 'paid')->exists();

            $data['publishTemplate'] = $publishTemplate;
            $data['alreadyLive'] = $alreadyLive;
            $data['needsCheckout'] = $needsCheckout;
            $data['isLocked'] = $publishTemplate && ! $publishTemplate->isPlaceholder() && ! $tenant->canActivateRealTemplate();
        }

        return view("website.setup.{$step}", $data);
    }

    public function updateBusinessType(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $data = $request->validate([
            'industry'       => 'required|string|max:100',
            'industry_other' => 'nullable|string|max:100|required_if:industry,other',
        ]);

        $tenant->update([
            'industry' => $data['industry'] === 'other' ? $data['industry_other'] : $data['industry'],
        ]);

        return redirect()->route('website.setup.show', ['step' => 'template']);
    }

    public function updateSubdomain(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $reserved = ['www', 'admin', 'api', 'mail', 'ftp', 'app', 'staging', 'billing', 'support'];

        $data = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9\-]+$/',
                "unique:tenants,subdomain,{$tenant->id}",
                function ($attribute, $value, $fail) use ($reserved) {
                    if (in_array(strtolower($value), $reserved, true)) {
                        $fail('That subdomain is reserved — please choose another.');
                    }
                },
            ],
        ]);

        $tenant->update(['subdomain' => $data['subdomain']]);

        return back()->with('success', "Subdomain set to {$data['subdomain']}." . config('app.domain', 'xquisite.co.za'));
    }

    public function updateCustomDomain(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $data = $request->validate([
            'custom_domain' => 'nullable|string|max:255',
        ]);

        $tenant->update([
            'custom_domain'          => $data['custom_domain'] ?: null,
            'custom_domain_verified' => false,
        ]);

        $message = $data['custom_domain']
            ? "Custom domain saved — it's pending verification, we'll be in touch."
            : 'Custom domain removed.';

        return back()->with('success', $message);
    }
}
