<?php

namespace Tests\Feature\Security;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Notifications\AppNotice;
use App\Notifications\SignInPausedNotification;
use App\Services\Security\LoginThrottleService;
use App\Services\Tenant\TenantContext;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SignInPauseFollowUpsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
        $this->tenant = Tenant::create(['name' => 'Test Salon', 'slug' => 'test-salon', 'is_active' => true]);
    }

    private function staff(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true] + $attributes);
        $user->assignRole($role);

        return $user;
    }

    private function failLogin(string $email, int $times): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
        }
    }

    // ── "Was this you?" and the owner alert ─────────────────────────────────

    public function test_a_paused_team_login_emails_the_holder_and_alerts_the_owner(): void
    {
        Notification::fake();
        $owner = $this->staff('tenant-owner');
        $employee = $this->staff('employee', ['name' => 'Lerato Dube']);

        $this->failLogin($employee->email, 3);

        Notification::assertSentTo($employee, SignInPausedNotification::class, function ($n) {
            return $n->attempts === 3 && $n->minutes === 5 && $n->resetUrl === route('password.request');
        });
        Notification::assertSentTo($owner, AppNotice::class, function ($n) use ($employee) {
            return str_contains($n->message, 'Lerato Dube') && str_contains($n->message, $employee->email) && $n->level === 'warning';
        });
    }

    public function test_the_owner_is_not_alerted_about_their_own_login(): void
    {
        Notification::fake();
        $owner = $this->staff('tenant-owner');

        $this->failLogin($owner->email, 3);

        Notification::assertSentTo($owner, SignInPausedNotification::class);
        Notification::assertNotSentTo($owner, AppNotice::class);
    }

    public function test_an_unknown_email_tells_nobody(): void
    {
        Notification::fake();
        $this->staff('tenant-owner');

        $this->failLogin('nobody@example.com', 3);

        Notification::assertNothingSent();
    }

    public function test_the_holder_gets_one_email_a_day_however_often_it_happens(): void
    {
        Notification::fake();
        $this->staff('tenant-owner');
        $employee = $this->staff('employee');

        $this->failLogin($employee->email, 3);
        $this->travel(LoginThrottleService::LOCK_SECONDS + 1)->seconds();
        $this->failLogin($employee->email, 3);

        Notification::assertSentToTimes($employee, SignInPausedNotification::class, 1);
    }

    public function test_a_paused_customer_gets_the_email_but_the_owner_is_left_alone(): void
    {
        Notification::fake();
        $owner = $this->staff('tenant-owner');
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Thandi', 'email' => 'thandi@example.com',
            'password' => Hash::make('password'), 'is_active' => true,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/book/test-salon/login', ['email' => 'thandi@example.com', 'password' => 'wrong']);
        }

        Notification::assertSentTo($customer, SignInPausedNotification::class, function ($n) {
            return $n->businessName === 'Test Salon' && $n->resetUrl === route('book.password.request', 'test-salon');
        });
        Notification::assertNotSentTo($owner, AppNotice::class);
    }

    public function test_the_email_reads_plainly(): void
    {
        $mail = (new SignInPausedNotification('team', 'Test Salon', 3, 5, '14:05', 'https://example.test/reset'))
            ->toMail(new User);

        $this->assertSame('Was this you? Sign-in to your Test Salon account was paused', $mail->subject);
        $this->assertStringContainsString('entered 3 times for your team login with Test Salon at 14:05 today', $mail->introLines[0]);
        $this->assertSame('https://example.test/reset', $mail->actionUrl);
    }

    public function test_a_failure_in_the_notifier_never_breaks_the_sign_in_form(): void
    {
        $employee = $this->staff('employee');
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('mail is down'));

        $this->failLogin($employee->email, 2);
        $this->post('/login', ['email' => $employee->email, 'password' => 'wrong-password'])->assertStatus(302);

        $this->assertSame('account', LoginThrottleService::lockedFor('127.0.0.1', 'staff', 'login', $employee->email)['scope']);
    }

    // ── Admin list and lift ─────────────────────────────────────────────────

    public function test_the_admin_sees_recent_pauses_and_can_lift_one(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');
        $employee = $this->staff('employee');

        $this->failLogin($employee->email, 3);
        TenantContext::clear();

        $this->actingAs($admin)->get(route('admin.blocked-ips.index'))
            ->assertOk()
            ->assertSee('Sign-in pauses, last 24 hours')
            ->assertSee($employee->email)
            ->assertSee('Team sign-in')
            ->assertSee('5 min left')
            ->assertSee('Lift now');

        $this->actingAs($admin)->post(route('admin.sign-in-pauses.lift'), [
            'scope' => 'account', 'ip' => '127.0.0.1', 'guard' => 'staff', 'channel' => 'login', 'email' => $employee->email,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'staff', 'login', $employee->email));
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'auth.sign_in_pause_lifted')->count());

        $this->actingAs($admin)->get(route('admin.blocked-ips.index'))
            ->assertSee('Ended')
            ->assertDontSee('Lift now');

        // And the person can sign in straight away, with a clean slate.
        auth()->logout();
        $this->post('/login', ['email' => $employee->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_a_whole_network_pause_is_listed_and_can_be_lifted(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');

        for ($i = 0; $i < LoginThrottleService::IP_THRESHOLD; $i++) {
            LoginThrottleService::recordFailure('127.0.0.1', 'staff', 'login', "guess{$i}@example.com");
        }

        $this->actingAs($admin)->get(route('admin.blocked-ips.index'))->assertOk()->assertSee('Whole network');

        $this->actingAs($admin)->post(route('admin.sign-in-pauses.lift'), ['scope' => 'ip', 'ip' => '127.0.0.1'])
            ->assertSessionHas('success');

        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'staff', 'login', null));
    }

    public function test_only_platform_admins_can_see_or_lift_pauses(): void
    {
        $owner = $this->staff('tenant-owner');

        $this->actingAs($owner)->get(route('admin.blocked-ips.index'))->assertForbidden();
        $this->actingAs($owner)->post(route('admin.sign-in-pauses.lift'), ['scope' => 'ip', 'ip' => '127.0.0.1'])->assertForbidden();
    }

    // ── Limits on register and confirm-password ─────────────────────────────

    public function test_customer_sign_ups_from_one_network_are_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/book/test-salon/register', [])->assertStatus(302);
        }

        $this->post('/book/test-salon/register', [])
            ->assertStatus(429)
            ->assertSee('Too many new accounts from your internet connection');
    }

    public function test_staff_sign_ups_from_one_network_are_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', [])->assertStatus(302);
        }

        $this->post('/register', [])->assertStatus(429);
    }

    public function test_confirm_password_guesses_are_limited_per_account_not_per_network(): void
    {
        $first = $this->staff('employee');
        $second = $this->staff('employee');

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($first)->post('/confirm-password', ['password' => 'wrong'])->assertStatus(302);
        }

        $this->actingAs($first)->post('/confirm-password', ['password' => 'password'])
            ->assertStatus(429)
            ->assertSee('Too many wrong passwords');

        // A colleague on the same address is not affected.
        $this->actingAs($second)->post('/confirm-password', ['password' => 'password'])
            ->assertSessionHasNoErrors();
    }

    // ── Cache clean-up ──────────────────────────────────────────────────────

    public function test_expired_cache_rows_are_pruned_and_live_ones_kept(): void
    {
        config(['cache.default' => 'database']);

        DB::table('cache')->insert([
            ['key' => 'old-one', 'value' => 'x', 'expiration' => time() - 10],
            ['key' => 'old-two', 'value' => 'x', 'expiration' => time() - 99999],
            ['key' => 'old-three', 'value' => 'x', 'expiration' => time() - 5],
            ['key' => 'live', 'value' => 'x', 'expiration' => time() + 600],
        ]);
        DB::table('cache_locks')->insert([
            ['key' => 'old-lock', 'owner' => 'a', 'expiration' => time() - 10],
            ['key' => 'live-lock', 'owner' => 'b', 'expiration' => time() + 600],
        ]);

        // A chunk smaller than the backlog proves the loop keeps going.
        $this->artisan('cache:prune-expired', ['--chunk' => 2])->assertSuccessful();

        $this->assertSame(['live'], DB::table('cache')->pluck('key')->all());
        $this->assertSame(['live-lock'], DB::table('cache_locks')->pluck('key')->all());
    }

    public function test_pruning_does_nothing_when_the_cache_is_not_in_the_database(): void
    {
        DB::table('cache')->insert(['key' => 'old', 'value' => 'x', 'expiration' => time() - 10]);

        $this->artisan('cache:prune-expired')->assertSuccessful();

        $this->assertSame(1, DB::table('cache')->count());
    }

    // ── Contact addresses ───────────────────────────────────────────────────

    public function test_privacy_and_legal_contacts_default_to_the_support_inbox(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('mailto:' . config('contact.privacy_email'), false)
            ->assertDontSee('xquisitecreations.co.za');
        $this->get('/terms')->assertOk()->assertSee('mailto:' . config('contact.legal_email'), false)
            ->assertDontSee('xquisitecreations.co.za');

        $this->assertSame(config('contact.support_email'), config('contact.privacy_email'));
        $this->assertSame(config('contact.support_email'), config('contact.legal_email'));
    }
}
