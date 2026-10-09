<?php

namespace Tests\Feature\Security;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Security\LoginThrottleService;
use App\Services\Tenant\TenantContext;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The owner gets the "locked out" call, so the owner (not only the platform
 * admin) can see sign-in pauses on their own team and let someone back in.
 */
class TeamSignInPausesTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
        $this->tenant = Tenant::create(['name' => 'Test Salon', 'slug' => 'test-salon', 'is_active' => true]);
        Notification::fake();
    }

    private function staff(string $role, ?Tenant $tenant = null, array $attributes = []): User
    {
        $user = User::factory()->create(['tenant_id' => ($tenant ?? $this->tenant)->id, 'is_active' => true] + $attributes);
        $user->assignRole($role);

        return $user;
    }

    private function pause(string $email): void
    {
        for ($i = 0; $i < LoginThrottleService::ACCOUNT_THRESHOLD; $i++) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
        }
        TenantContext::clear();
    }

    public function test_the_team_page_is_quiet_when_nobody_is_paused(): void
    {
        $owner = $this->staff('tenant-owner');

        $this->actingAs($owner)->get(route('admin.users.index'))->assertOk()->assertDontSee('Sign-in pauses, last 24 hours');
    }

    public function test_the_owner_sees_a_paused_team_member_and_lets_them_back_in(): void
    {
        $owner = $this->staff('tenant-owner');
        $employee = $this->staff('employee', null, ['name' => 'Lerato Dube', 'email' => 'Lerato.Dube@example.com']);
        $this->pause($employee->email);

        $this->actingAs($owner)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Sign-in pauses, last 24 hours')
            ->assertSee('Lerato Dube')
            ->assertSee('5 min left')
            ->assertSee('Let them in now');

        $this->actingAs($owner)->post(route('admin.users.sign-in-pauses.lift'), ['ip' => '127.0.0.1', 'user_id' => $employee->id])
            ->assertRedirect()->assertSessionHas('success', 'Lerato Dube can sign in again now.');

        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'staff', 'login', $employee->email));
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'auth.sign_in_pause_lifted')->where('entity_id', $employee->id)->count());

        $this->actingAs($owner)->get(route('admin.users.index'))->assertSee('Ended')->assertDontSee('Let them in now');

        auth()->logout();
        $this->post('/login', ['email' => $employee->email, 'password' => 'password'])->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_an_owner_never_sees_or_lifts_pauses_at_another_business(): void
    {
        $owner = $this->staff('tenant-owner');
        $other = Tenant::create(['name' => 'Other Salon', 'slug' => 'other-salon', 'is_active' => true]);
        $stranger = $this->staff('employee', $other, ['name' => 'Someone Else']);
        $this->pause($stranger->email);

        $this->actingAs($owner)->get(route('admin.users.index'))->assertOk()->assertDontSee('Someone Else')->assertDontSee('Sign-in pauses, last 24 hours');

        $this->actingAs($owner)->post(route('admin.users.sign-in-pauses.lift'), ['ip' => '127.0.0.1', 'user_id' => $stranger->id])
            ->assertSessionHasErrors('user_id');
        $this->assertNotNull(LoginThrottleService::lockedFor('127.0.0.1', 'staff', 'login', $stranger->email));
    }

    public function test_pauses_on_unknown_emails_stay_off_the_team_page(): void
    {
        $owner = $this->staff('tenant-owner');
        $this->pause('nobody@example.com');

        $this->actingAs($owner)->get(route('admin.users.index'))->assertOk()->assertDontSee('Sign-in pauses, last 24 hours');
    }

    public function test_an_employee_cannot_lift_a_pause(): void
    {
        $employee = $this->staff('employee');
        $colleague = $this->staff('employee');
        $this->pause($colleague->email);

        $this->actingAs($employee)->post(route('admin.users.sign-in-pauses.lift'), ['ip' => '127.0.0.1', 'user_id' => $colleague->id])
            ->assertForbidden();
        $this->assertNotNull(LoginThrottleService::lockedFor('127.0.0.1', 'staff', 'login', $colleague->email));
    }
}
