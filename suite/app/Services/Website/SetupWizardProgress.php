<?php

namespace App\Services\Website;

use App\Models\Tenant;

class SetupWizardProgress
{
    /**
     * Ordered step keys. "welcome" is an uncounted intro screen (rendered
     * first, never marked done) — the 13 counted steps are business-type
     * through publish.
     */
    public const STEPS = [
        'welcome',
        'business-type',
        'template',
        'logo',
        'colours',
        'fonts',
        'business-info',
        'contact',
        'social',
        'booking',
        'store',
        'domain',
        'preview',
        'publish',
    ];

    public function __construct(private readonly Tenant $tenant)
    {
    }

    /**
     * Whether each counted step is "done" — derived entirely from real data,
     * never from a separate progress table. Used both for the step
     * indicator/checklist and to pick a resume point; it never blocks
     * forward navigation to any step.
     */
    public function completed(): array
    {
        $tenant = $this->tenant;
        $branding = $tenant->branding;
        $activeTemplate = $tenant->activeTemplate?->template;

        return [
            'business-type' => ! empty($tenant->industry),
            'template'      => ! empty($tenant->preferred_template_key)
                || ($activeTemplate && ! $activeTemplate->isPlaceholder()),
            'logo'          => ! empty($tenant->logo_url),
            'colours'       => ! empty($branding?->primary_color),
            'fonts'         => ! empty($branding?->heading_font),
            'business-info' => ! empty($branding?->description),
            'contact'       => ! empty($branding?->contact_email)
                || ! empty($branding?->contact_phone)
                || ! empty($branding?->whatsapp_number)
                || ! empty($branding?->business_hours),
            'social'        => ! empty(array_filter($branding?->socials ?? [])),
            'booking'       => $tenant->hasModule('booking') || $tenant->moduleRequests()->where('module', 'booking')->exists(),
            'store'         => $tenant->hasModule('pos') || $tenant->moduleRequests()->where('module', 'pos')->exists(),
            'domain'        => ! empty($tenant->subdomain) || ! empty($tenant->custom_domain),
            'preview'       => ! empty($tenant->preferred_template_key) || ($activeTemplate && ! $activeTemplate->isPlaceholder()),
            'publish'       => (bool) ($activeTemplate && ! $activeTemplate->isPlaceholder()),
        ];
    }

    public function countedSteps(): array
    {
        return array_values(array_diff(self::STEPS, ['welcome']));
    }

    public function doneCount(): int
    {
        return count(array_filter($this->completed()));
    }

    public function totalCount(): int
    {
        return count($this->countedSteps());
    }

    public function isComplete(): bool
    {
        return $this->doneCount() === $this->totalCount();
    }

    /**
     * First incomplete step, in wizard order — 'welcome' if nothing at all
     * is started, or 'publish' if everything else is already done.
     */
    public function resumeStep(): string
    {
        $completed = $this->completed();

        if (! array_filter($completed)) {
            return 'welcome';
        }

        foreach ($this->countedSteps() as $step) {
            if (! $completed[$step]) {
                return $step;
            }
        }

        return 'publish';
    }
}
