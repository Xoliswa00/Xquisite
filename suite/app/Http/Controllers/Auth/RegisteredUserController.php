<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeNewUserMail;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NewTenantRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $templates = Template::visible()->active()->ordered()->get();

        // Loosely maps the registration "industry" options to a template
        // category, so step 2 can pre-select the closest-matching tab.
        $industryCategoryMap = [
            'salon' => 'beauty-spa', 'spa' => 'beauty-spa', 'barbershop' => 'beauty-spa',
            'beauty' => 'beauty-spa', 'nail' => 'beauty-spa', 'massage' => 'beauty-spa',
            'fitness' => 'fitness',
            'hospitality' => 'restaurant',
            'events' => 'wedding-events',
        ];

        return view('auth.register', compact('templates', 'industryCategoryMap'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'industry'      => ['nullable', 'string', 'max:100'],
            'industry_other' => ['nullable', 'string', 'max:100', 'required_if:industry,other'],
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'template_key'  => ['nullable', 'string', 'exists:templates,key'],
        ]);

        $slug = $this->uniqueSlug($request->business_name);

        $industry = $request->industry === 'other'
            ? $request->industry_other
            : $request->industry;

        $template = $request->filled('template_key')
            ? Template::where('key', $request->template_key)->where('is_active', true)->where('is_visible', true)->first()
            : null;

        [$tenant, $user] = DB::transaction(function () use ($request, $slug, $industry, $template) {
            $tenant = Tenant::create([
                'name'          => $request->business_name,
                'slug'          => $slug,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'industry'      => $industry,
                'plan'          => 'starter',
                'is_active'     => true,
                'trial_ends_at' => now()->addDays(14),
            ]);

            $user = User::create([
                'name'               => $request->name,
                'email'              => $request->email,
                'password'           => Hash::make($request->password),
                'tenant_id'          => $tenant->id,
                'email_verified_at'  => now(),
            ]);

            $user->assignRole('tenant-owner');

            if ($template && $template->isFree()) {
                $tenant->activateTemplate($template->key, $user->id);
                $tenant->branding()->create([]);
            }

            return [$tenant, $user];
        });

        event(new Registered($user));

        Mail::to($user->email)->queue(new WelcomeNewUserMail($user));

        // Notify platform operators of new signup
        User::role('super-admin')->each(
            fn($platformOwner) => $platformOwner->notify(new NewTenantRegistered($tenant, $user))
        );

        Auth::login($user);

        if ($template && $template->isFree()) {
            return redirect()->route('website.branding.edit')
                ->with('success', "Welcome! {$template->name} is now live — let's add your branding.");
        }

        return redirect()->route('dashboard');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
