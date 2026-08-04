<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $branding = $tenant->branding ?? $tenant->branding()->create([]);
        $fonts = config('branding.fonts');

        return view('website.branding.edit', compact('tenant', 'branding', 'fonts'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $fontKeys = implode(',', array_keys(config('branding.fonts')));

        $data = $request->validate([
            'primary_color'   => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'heading_font'    => "nullable|in:{$fontKeys}",
            'body_font'       => "nullable|in:{$fontKeys}",
            'description'     => 'nullable|string|max:1000',
            'contact_email'   => 'nullable|email|max:100',
            'contact_phone'   => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'socials'         => 'nullable|array',
            'socials.*'       => 'nullable|url|max:255',
            'business_hours'  => 'nullable|array',
        ]);

        $tenant->branding()->updateOrCreate(['tenant_id' => $tenant->id], $data);

        return Redirect::route('website.branding.edit')->with('success', 'Branding updated.');
    }

    public function favicon(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $request->validate([
            'favicon' => 'required|image|mimes:jpeg,jpg,png,webp,ico|max:2048',
        ]);

        $branding = $tenant->branding ?? $tenant->branding()->create([]);

        if ($branding->favicon_url && str_starts_with($branding->favicon_url, '/storage/')) {
            Storage::delete(str_replace('/storage/', 'public/', $branding->favicon_url));
        }

        $path = $request->file('favicon')->store("public/branding/{$tenant->id}");
        $branding->update(['favicon_url' => Storage::url($path)]);

        return Redirect::route('website.branding.edit')->with('success', 'Favicon updated.');
    }

    public function heroImage(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $request->validate([
            'hero_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $branding = $tenant->branding ?? $tenant->branding()->create([]);

        if ($branding->hero_image_url && str_starts_with($branding->hero_image_url, '/storage/')) {
            Storage::delete(str_replace('/storage/', 'public/', $branding->hero_image_url));
        }

        $path = $request->file('hero_image')->store("public/branding/{$tenant->id}");
        $branding->update(['hero_image_url' => Storage::url($path)]);

        return Redirect::route('website.branding.edit')->with('success', 'Hero image updated.');
    }
}
