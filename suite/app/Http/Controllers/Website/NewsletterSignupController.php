<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSignup;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterSignupController extends Controller
{
    public function store(Request $request, string $tenantSlug): RedirectResponse
    {
        $tenant = Tenant::where('slug', strtolower($tenantSlug))->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        NewsletterSignup::firstOrCreate([
            'tenant_id' => $tenant->id,
            'email'     => $validated['email'],
        ]);

        return back()->with('newsletter_success', true);
    }
}
