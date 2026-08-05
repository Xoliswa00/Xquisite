<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateReview;
use App\Models\TenantTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TemplateReviewController extends Controller
{
    public function store(Request $request, Template $template): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        // Only tenants who have actually activated this template can review it
        // — keeps ratings meaningful ("verified use") rather than open to anyone.
        $hasUsed = TenantTemplate::where('tenant_id', $tenant->id)
            ->where('template_key', $template->key)
            ->exists();

        abort_unless($hasUsed, 403, 'Activate this template before reviewing it.');

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:120',
            'body'   => 'nullable|string|max:2000',
        ]);

        TemplateReview::updateOrCreate(
            ['template_key' => $template->key, 'tenant_id' => $tenant->id],
            [
                'user_id' => $request->user()->id,
                'rating'  => $data['rating'],
                'title'   => $data['title'] ?? null,
                'body'    => $data['body'] ?? null,
                'status'  => 'pending',
            ]
        );

        return redirect()->route('website.templates.show', $template)
            ->with('success', 'Thanks — your review is submitted and will appear once approved.');
    }
}
