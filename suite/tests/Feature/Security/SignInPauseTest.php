<?php

namespace Tests\Feature\Security;

use App\Models\BlockedIp;
use App\Models\User;
use App\Services\Security\LoginThrottleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $response->assertSee('paused after 3 failed attempts');
        $response->assertSee('about 5 minutes');
        $this->assertGuest();

        // Same IP, different account: unaffected.
        $this->post('/login', ['email' => $colleague->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($colleague);
    }

    public function test_a_paused_account_does_not_take_the_rest_of_the_site_down(): void
    {
        $user = User::factory()->create();

        $this->failLogin($user->email, 3);

        $this->get('/login')->assertOk();
        $this->assertFalse(BlockedIp::isBlocked('127.0.0.1'));
        $this->assertDatabaseCount('blocked_ips', 0);
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
            LoginThrottleService::recordFailure('127.0.0.1', 'staff', "guess{$i}@example.com");
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(429)
            ->assertSee('too many failed attempts from your network');
        $this->assertGuest();

        $this->get('/login')->assertOk();
        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_failed_sign_ins_do_not_pause_password_resets(): void
    {
        for ($i = 0; $i < LoginThrottleService::ACCOUNT_THRESHOLD; $i++) {
            LoginThrottleService::recordFailure('127.0.0.1', 'customer', 'Someone@Example.com');
        }

        $this->assertSame('account', LoginThrottleService::lockedFor('127.0.0.1', 'login', 'someone@example.com')['scope']);
        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'reset', 'someone@example.com'));
        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.2', 'login', 'someone@example.com'));
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

    public function test_a_manually_blocked_ip_is_told_why_and_until_when(): void
    {
        BlockedIp::block('127.0.0.1', 'Internal note that must not be shown', null, 30);

        $response = $this->get('/login');

        $response->assertStatus(403);
        $response->assertSee('IP address 127.0.0.1');
        $response->assertSee('blocked by the site administrator');
        $response->assertSee('The block lifts');
        $response->assertDontSee('Internal note');

        $this->assertSame(1, DB::table('audit_logs')->where('action', 'security.blocked_ip_refused')->count());
    }

    public function test_a_blocked_ip_gets_the_reason_as_json_too(): void
    {
        BlockedIp::block('127.0.0.1', 'manual');

        $this->getJson('/login')
            ->assertStatus(403)
            ->assertJsonPath('error', 'Access denied.')
            ->assertJsonFragment(['message' => 'Access from your network (IP address 127.0.0.1) has been blocked by the site administrator. It stays in place until an administrator removes it. If you think this is a mistake, contact support and quote this IP address.']);
    }
}
