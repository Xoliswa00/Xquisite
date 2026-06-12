<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use App\Models\Company;
use app\Models\company_user;
use App\Models\company_invitations;
use App\Models\CompanyInvitation;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
public function create(array $input): User
{
    Validator::make($input, [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => $this->passwordRules(),
    ])->validate();

    return DB::transaction(function () use ($input) {

        // 1️⃣ Create global identity
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // 2️⃣ Invitation check (highest priority)
        $invitation = company_invitations::where('email', $user->email)
            ->where('expires_at', '>', now())
            ->first();

        if ($invitation) {

            $company = $invitation->company;

            $company->users()->attach($user->id, [
                'role' => $invitation->role,
            ]);

            $user->current_company_id = $company->id;
            $user->save();

            $invitation->delete();

            return $user;
        }

        // 3️⃣ Domain auto-join (if corporate email)
        $domain = Str::after($user->email, '@');

        if (!$this->isPublicEmailDomain($domain)) {

            $company = Company::where('domain', $domain)
                ->where('domain_verified_at', '!=', null)
                ->first();

         
            if ($company) {

                $company->users()->attach($user->id, [
                    'role' => 'viewer',
                ]);

                $user->current_company_id = $company->id;
                $user->save();

                return $user;
            }
        }

        // 4️⃣ Fallback: Create new company (Founder Flow)

        $company = Company::create([
            'name' => $this->deriveCompanyName($user->name),
            'slug' => $this->generateUniqueSlug($user->name),
            'status' => 'active',
            'plan' => 'starter',
        ]);

      if ($company) {

    DB::table('company_users')->insert([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 'viewer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user->current_company_id = $company->id;
    $user->save();

    return $user;
}

        $user->current_company_id = $company->id;
        $user->save();

        return $user;
    });
}
private function generateUniqueSlug(string $name): string
{
    $base = Str::slug($name);
    $slug = $base;
    $counter = 1;

    while (Company::where('slug', $slug)->exists()) {
        $slug = "{$base}-{$counter}";
        $counter++;
    }

    return $slug;
}
private function deriveCompanyName(string $name): string
{
    // Use user name as default company name, could refine later
    return $name . ' Co.';
}
private function isPublicEmailDomain(string $domain): bool
{
    $blocked = [
        'gmail.com',
        'outlook.com',
        'hotmail.com',
        'yahoo.com',
        'icloud.com',
        'live.com',
        'aol.com',
        'proton.me',
    ];

    return in_array(strtolower($domain), $blocked);
}
}
