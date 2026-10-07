<?php

namespace Tests\Feature\Security;

use App\Models\BlockedIp;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Services\Security\LoginThrottleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SignInPauseTest extends TestCase
{
    use RefreshDatabase;

    private function failLogin(string $email, int $times = 1): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
        }
    }

    public function test_three_failures_pause_only_that_account_on_a_shared_ip(): void
    {
        $locked = User::factory()->create();
        $colleague = User::factory()->create();

        $this->failLogin($locked->email, 3);

        $response = $this->post('/login', ['email' => $locked->email, 'password' => 'password']);
        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
        $response->assertSee("There were 3 failed sign-in attempts for {$locked->email}");
        $response->assertSee('about 5 minutes');
        $response->assertSee(route('password.request'));
        $response->assertSee(route('login'));
        $this->assertGuest();

        // Same IP, different account: unaffected.
        $this->post('/login', ['email' => $colleague->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($colleague);
    }

    public function test_the_pause_is_announced_before_it_lands(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'nope'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $second = $this->post('/login', ['email' => $user->email, 'password' => 'nope']);
        $this->assertStringContainsString('One more failed attempt will pause sign-in', session('errors')->first('email'));

        $third = $this->post('/login', ['email' => $user->email, 'password' => 'nope']);
        $third->assertStatus(302);
        $this->assertStringContainsString('is now paused for 5 minutes', session('errors')->first('email'));
    }

    public function test_a_paused_account_does_not_take_the_rest_of_the_site_down(): void
    {
        $user = User::factory()->create();

        $this->failLogin($user->email, 3);

        $this->get('/login')->assertOk();
        $this->assertFalse(BlockedIp::isBlocked('127.0.0.1'));
        $this->assertDatabaseCount('blocked_ips', 0);
    }

    public function test_retrying_a_paused_account_does_not_use_up_the_networks_login_allowance(): void
    {
        $paused = User::factory()->create();
        $colleague = User::factory()->create();

        $this->failLogin($paused->email, 3);

        // More than the 10-per-minute-per-IP `auth` limiter allows.
        for ($i = 0; $i < 12; $i++) {
            $this->post('/login', ['email' => $paused->email, 'password' => 'password'])
                ->assertStatus(429)
                ->assertSee('Sign-in paused');
        }

        $this->post('/login', ['email' => $colleague->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_the_pause_lifts_after_five_minutes(): void
    {
        $user = User::factory()->create();

        $this->failLogin($user->email, 3);
        $this->travel(LoginThrottleService::LOCK_SECONDS + 1)->seconds();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_a_correct_sign_in_clears_earlier_strikes(): void
    {
        $user = User::factory()->create();

        $this->failLogin($user->email, 2);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/logout');

        $this->failLogin($user->email, 2);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_many_failures_across_accounts_pause_sign_in_for_the_ip_but_not_the_site(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < LoginThrottleService::IP_THRESHOLD; $i++) {
            LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', "guess{$i}@example.com");
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(429)
            ->assertSee('too many failed sign-in attempts from your internet connection');
        $this->assertGuest();

        $this->get('/login')->assertOk();
        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_an_ip_wide_pause_is_logged_once_whatever_email_is_submitted(): void
    {
        for ($i = 0; $i < LoginThrottleService::IP_THRESHOLD; $i++) {
            LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', "guess{$i}@example.com");
        }

        foreach (['a@example.com', 'b@example.com', 'c@example.com'] as $email) {
            $this->post('/reset-password', ['email' => $email, 'token' => 'x', 'password' => 'x'])->assertStatus(429);
        }

        $this->assertSame(1, DB::table('audit_logs')->where('action', 'auth.sign_in_refused')->count());
    }

    public function test_strikes_are_separate_per_channel_guard_and_ip(): void
    {
        for ($i = 0; $i < LoginThrottleService::ACCOUNT_THRESHOLD; $i++) {
            LoginThrottleService::recordFailure('127.0.0.1', 'customer', 'login', 'Someone@Example.com');
        }

        $this->assertSame('account', LoginThrottleService::lockedFor('127.0.0.1', 'customer', 'login', 'someone@example.com')['scope']);
        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'customer', 'reset', 'someone@example.com'));
        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'staff', 'login', 'someone@example.com'));
        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.2', 'customer', 'login', 'someone@example.com'));
    }

    public function test_signing_in_on_another_portal_does_not_clear_the_strikes(): void
    {
        LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', 'owner@example.com');
        LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', 'owner@example.com');

        LoginThrottleService::recordSuccess('127.0.0.1', 'customer', 'owner@example.com');

        $this->assertSame(0, LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', 'owner@example.com'));
    }

    public function test_accented_variants_of_an_email_share_one_set_of_strikes(): void
    {
        LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', 'admin@example.com');
        LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', 'ádmin@example.com');

        $this->assertSame(0, LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', 'ADMÍN@example.com'));
    }

    public function test_an_unknown_guard_or_channel_fails_loudly(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'resets', 'a@example.com');
    }

    public function test_pauses_and_refusals_are_written_to_the_audit_log_once(): void
    {
        $user = User::factory()->create();

        $this->failLogin($user->email, 3);
        $this->failLogin($user->email, 2);

        $paused = DB::table('audit_logs')->where('action', 'auth.sign_in_paused')->get();
        $refused = DB::table('audit_logs')->where('action', 'auth.sign_in_refused')->get();

        $this->assertCount(1, $paused);
        $this->assertCount(1, $refused);
        $this->assertSame($user->email, json_decode($refused->first()->meta, true)['email']);
    }

    public function test_the_customer_portal_pauses_and_points_back_to_its_own_pages(): void
    {
        $tenant = Tenant::create(['name' => 'Test Salon', 'slug' => 'test-salon', 'is_active' => true]);
        Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Thandi',
            'email' => 'thandi@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/book/test-salon/login', ['email' => 'thandi@example.com', 'password' => 'wrong']);
        }

        $this->post('/book/test-salon/login', ['email' => 'thandi@example.com', 'password' => 'password'])
            ->assertStatus(429)
            ->assertSee(route('book.password.request', 'test-salon'))
            ->assertSee(route('book.login', 'test-salon'));

        $paused = DB::table('audit_logs')->where('action', 'auth.sign_in_paused')->first();
        $this->assertSame('test-salon', json_decode($paused->meta, true)['tenant_slug']);

        // The forgot-password form stays open during a sign-in pause.
        $this->post('/book/test-salon/forgot-password', ['email' => 'thandi@example.com'])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();
    }

    public function test_asking_for_a_reset_link_again_too_soon_is_not_a_strike(): void
    {
        $tenant = Tenant::create(['name' => 'Test Salon', 'slug' => 'test-salon', 'is_active' => true]);
        Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Thandi',
            'email' => 'thandi@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // First request sends the link; the rest hit the broker's resend throttle.
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/book/test-salon/forgot-password', ['email' => 'thandi@example.com']);
            $response->assertStatus(302);
        }

        $response->assertSessionHasErrors(['email' => __(Password::RESET_THROTTLED)]);
        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'customer', 'reset', 'thandi@example.com'));
    }

    /**
     * The route half of the contract: a new portal that forgets the middleware,
     * or mistypes its guard or channel, fails here.
     */
    public function test_every_sign_in_and_reset_post_is_guarded(): void
    {
        $guards = ['book/' => 'customer', 'rent/' => 'renter', 'contractor/' => 'contractor'];
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            if (! in_array('POST', $route->methods(), true)
                || ! preg_match('#(^|/)(login|forgot-password|reset-password)$#', $route->uri(), $m)
                || preg_match('#/users/\{user\}/reset-password$#', $route->uri())) {
                continue;
            }

            $guard = 'staff';
            foreach ($guards as $prefix => $name) {
                if (str_starts_with($route->uri(), $prefix)) {
                    $guard = $name;
                }
            }
            $channel = $m[2] === 'login' ? 'login' : 'reset';
            $middleware = $route->gatherMiddleware();

            $this->assertContains("sign-in.pause:{$guard},{$channel}", $middleware, "POST {$route->uri()} is missing sign-in.pause:{$guard},{$channel}");
            $this->assertContains('throttle:auth', $middleware, "POST {$route->uri()} is missing throttle:auth");
            $this->assertLessThan(
                array_search('throttle:auth', $middleware, true),
                array_search("sign-in.pause:{$guard},{$channel}", $middleware, true),
                "POST {$route->uri()} must run sign-in.pause before throttle:auth",
            );
            $checked++;
        }

        $this->assertSame(12, $checked);
    }

    public function test_a_manually_blocked_ip_is_told_why_and_until_when(): void
    {
        BlockedIp::block('127.0.0.1', 'Internal note that must not be shown', null, 30);

        $response = $this->get('/login');

        $response->assertStatus(403);
        $response->assertSee('Xquisite Creations has blocked access from your internet connection');
        $response->assertSee('The block lifts');
        $response->assertSee('give this reference');
        $response->assertSee('127.0.0.1');
        $response->assertSee(config('contact.support_email'));
        $response->assertDontSee('Internal note');

        $this->assertSame(1, DB::table('audit_logs')->where('action', 'security.blocked_ip_refused')->count());
    }

    public function test_a_blocked_ip_gets_the_reason_as_json_too(): void
    {
        BlockedIp::block('127.0.0.1', 'manual');

        $this->getJson('/login')
            ->assertStatus(403)
            ->assertJsonPath('error', 'Access denied.')
            ->assertJsonPath('reference', '127.0.0.1')
            ->assertJsonPath('message', 'Xquisite Creations has blocked access from your internet connection. It stays in place until we remove it.');
    }
}
