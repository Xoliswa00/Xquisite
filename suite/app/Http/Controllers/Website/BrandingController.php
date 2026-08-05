<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\TenantSectionSeeder;
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

        foreach (['description', 'contact_email', 'contact_phone', 'whatsapp_number', 'socials', 'business_hours'] as $field) {
            if (array_key_exists($field, $data)) {
                TenantSectionSeeder::syncBrandingFieldToSections($tenant, $field, $data[$field]);
            }
        }

        return Redirect::back()->with('success', 'Branding updated.');
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
        $url = Storage::url($path);
        $branding->update(['hero_image_url' => $url]);
        TenantSectionSeeder::syncBrandingFieldToSections($tenant, 'hero_image_url', $url);

        return Redirect::route('website.branding.edit')->with('success', 'Hero image updated.');
    }

    public function aboutImage(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $request->validate([
            'about_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $branding = $tenant->branding ?? $tenant->branding()->create([]);

        if ($branding->about_image_url && str_starts_with($branding->about_image_url, '/storage/')) {
            Storage::delete(str_replace('/storage/', 'public/', $branding->about_image_url));
        }

        $path = $request->file('about_image')->store("public/branding/{$tenant->id}");
        $url = Storage::url($path);
        $branding->update(['about_image_url' => $url]);
        TenantSectionSeeder::syncBrandingFieldToSections($tenant, 'about_image_url', $url);

        return Redirect::route('website.branding.edit')->with('success', 'About photo updated.');
    }

    public function galleryImageStore(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $branding = $tenant->branding ?? $tenant->branding()->create([]);
        $gallery = $branding->gallery_images ?? [];

        if (count($gallery) >= 8) {
            return Redirect::route('website.branding.edit')->with('error', 'You can have up to 8 gallery photos — remove one before adding another.');
        }

        $request->validate([
            'gallery_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $path = $request->file('gallery_image')->store("public/branding/{$tenant->id}/gallery");
        $gallery[] = Storage::url($path);
        $branding->update(['gallery_images' => $gallery]);
        TenantSectionSeeder::syncBrandingFieldToSections($tenant, 'gallery_images', $gallery);

        return Redirect::route('website.branding.edit')->with('success', 'Gallery photo added.');
    }

    public function galleryImageDestroy(Request $request, int $index): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $branding = $tenant->branding;
        abort_unless($branding, 404);

        $gallery = $branding->gallery_images ?? [];
        abort_unless(array_key_exists($index, $gallery), 404);

        $url = $gallery[$index];
        if (str_starts_with($url, '/storage/')) {
            Storage::delete(str_replace('/storage/', 'public/', $url));
        }

        unset($gallery[$index]);
        $gallery = array_values($gallery);
        $branding->update(['gallery_images' => $gallery]);
        TenantSectionSeeder::syncBrandingFieldToSections($tenant, 'gallery_images', $gallery);

        return Redirect::route('website.branding.edit')->with('success', 'Gallery photo removed.');
    }
}
