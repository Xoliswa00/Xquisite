<?php

namespace Tests\Feature\Admin;

use App\Mail\WelcomeStaffEmail;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The tenant "Staff" screen (admin/users). Reachable by any tenant user holding
 * `manage-staff` — not just platform operators — so it must stay scoped to the
 * owner's own tenant, must not grant platform permissions, must not let one
 * manager act on another, and the owner-set-password path must not depend on a
 * working inbox.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /** Permissions the screen may assign — platform perms and manage-staff excluded. */
    private array $assignable = [
        'manage-products', 'manage-orders', 'manage-appointments',
        'manage-customers', 'manage-properties', 'view-reports',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    private function tenant(string $slug = 'test-salon'): Tenant
    {
        return Tenant::create(['name' => ucfirst($slug), 'slug' => $slug, 'is_active' => true]);
    }

    private function userIn(Tenant $tenant, string $role): User
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        return $user;
    }

    // ── Access to the screen ────────────────────────────────────────────

    public function test_user_without_manage_staff_is_denied(): void
    {
        $tenant = $this->tenant();

        $this->actingAs($this->userIn($tenant, 'employee'))
            ->get('/admin/users')->assertForbidden();
    }

    public function test_owner_and_manager_can_open_the_screen(): void
    {
        $tenant = $this->tenant();

        $this->actingAs($this->userIn($tenant, 'tenant-owner'))->get('/admin/users')->assertOk();
        $this->actingAs($this->userIn($tenant, 'manager'))->get('/admin/users')->assertOk();
        $this->actingAs($this->userIn($tenant, 'tenant-owner'))->get('/admin/users/team-guide')->assertOk();
    }

    // ── Permission list ────────────────────────────────────────────────

    public function test_create_form_offers_only_assignable_permissions(): void
    {
        $response = $this->actingAs($this->userIn($this->tenant(), 'tenant-owner'))
            ->get('/admin/users/create');

        $response->assertOk();
        foreach ($this->assignable as $permission) {
            $response->assertSee('value="' . $permission . '"', false);
        }
        $response->assertDontSee('value="manage-staff"', false);
        $response->assertDontSee('value="manage-tenants"', false);
    }

    public function test_owner_cannot_grant_manage_staff_or_platform_permissions(): void
    {
        $owner = $this->userIn($this->tenant(), 'tenant-owner');

        foreach (['manage-staff', 'manage-tenants'] as $forbidden) {
            $this->actingAs($owner)->post('/admin/users', [
                'name' => 'Escalation',
                'email' => "esc-{$forbidden}@test-salon.test",
                'role' => 'employee',
                'password' => 'handover-pass-1',
                'password_confirmation' => 'handover-pass-1',
                'permissions' => [$forbidden],
            ])->assertSessionHasErrors('permissions.0');

            $this->assertDatabaseMissing('users', ['email' => "esc-{$forbidden}@test-salon.test"]);
        }
    }

    // ── Credential handling ───────────────────────────────────────────

    public function test_owner_set_password_creates_a_verified_account_with_no_email(): void
    {
        Mail::fake();
        $owner = $this->userIn($this->tenant(), 'tenant-owner');

        $response = $this->actingAs($owner)->post('/admin/users', [
            'name' => 'Thandi',
            'email' => 'thandi@test-salon.test',
            'role' => 'employee',
            'password' => 'handover-pass-1',
            'password_confirmation' => 'handover-pass-1',
        ]);

        $response->assertSessionHasNoErrors()->assertSessionHas('temp_password', 'handover-pass-1');

        $staff = User::where('email', 'thandi@test-salon.test')->first();
        $this->assertSame($owner->tenant_id, $staff->tenant_id);
        $this->assertTrue($staff->hasRole('employee'));
        $this->assertTrue($staff->require_password_change);
        $this->assertNotNull($staff->email_verified_at, 'owner-created account should not need email verification');
        $this->assertTrue(Hash::check('handover-pass-1', $staff->password));

        Mail::assertNothingQueued();
    }

    public function test_blank_password_generates_one_shown_to_the_owner_and_emailed(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->userIn($this->tenant(), 'tenant-owner'))->post('/admin/users', [
            'name' => 'No Inbox',
            'email' => 'temp@test-salon.test',
            'role' => 'employee',
        ]);

        $response->assertRedirect();
        $this->assertNotEmpty($response->getSession()->get('temp_password'));

        $staff = User::where('email', 'temp@test-salon.test')->first();
        $this->assertTrue($staff->require_password_change);
        $this->assertNotNull($staff->email_verified_at);
        Mail::assertQueued(WelcomeStaffEmail::class);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->actingAs($this->userIn($this->tenant(), 'tenant-owner'))->post('/admin/users', [
            'name' => 'Mismatch',
            'email' => 'mismatch@test-salon.test',
            'role' => 'employee',
            'password' => 'aaaaaaaa',
            'password_confirmation' => 'bbbbbbbb',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'mismatch@test-salon.test']);
    }

    public function test_reset_password_returns_the_new_password_to_the_owner(): void
    {
        $tenant = $this->tenant();
        $owner = $this->userIn($tenant, 'tenant-owner');
        $staff = $this->userIn($tenant, 'employee');
        $staff->update(['require_password_change' => false]);

        $response = $this->actingAs($owner)
            ->post("/admin/users/{$staff->id}/reset-password");

        $response->assertSessionHasNoErrors();
        $new = $response->getSession()->get('temp_password');
        $this->assertNotEmpty($new);

        $staff->refresh();
        $this->assertTrue($staff->require_password_change);
        $this->assertTrue(Hash::check($new, $staff->password));
    }

    // ── Tenant + role boundaries ──────────────────────────────────────

    public function test_owner_cannot_touch_another_tenants_users(): void
    {
        $owner = $this->userIn($this->tenant('salon-a'), 'tenant-owner');
        $outsider = $this->userIn($this->tenant('salon-b'), 'employee');

        $this->actingAs($owner)->get("/admin/users/{$outsider->id}/edit")->assertForbidden();
        $this->actingAs($owner)->post("/admin/users/{$outsider->id}/reset-password")->assertForbidden();
    }

    public function test_a_manager_cannot_act_on_another_manager(): void
    {
        $tenant = $this->tenant();
        $actingManager = $this->userIn($tenant, 'manager');
        $peerManager = $this->userIn($tenant, 'manager');

        $this->actingAs($actingManager)
            ->post("/admin/users/{$peerManager->id}/deactivate")->assertForbidden();
        $this->actingAs($actingManager)
            ->post("/admin/users/{$peerManager->id}/reset-password")->assertForbidden();
    }

    public function test_owner_can_act_on_a_manager(): void
    {
        $tenant = $this->tenant();
        $owner = $this->userIn($tenant, 'tenant-owner');
        $manager = $this->userIn($tenant, 'manager');

        $this->actingAs($owner)
            ->post("/admin/users/{$manager->id}/deactivate")->assertRedirect();

        $this->assertFalse($manager->fresh()->is_active);
    }

    public function test_a_manager_can_still_manage_employees(): void
    {
        $tenant = $this->tenant();
        $manager = $this->userIn($tenant, 'manager');
        $employee = $this->userIn($tenant, 'employee');

        $this->actingAs($manager)
            ->post("/admin/users/{$employee->id}/deactivate")->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);
    }

    public function test_the_owner_account_is_never_mutable_here(): void
    {
        $tenant = $this->tenant();
        $owner = $this->userIn($tenant, 'tenant-owner');
        $secondOwner = $this->userIn($tenant, 'tenant-owner');

        $this->actingAs($owner)
            ->post("/admin/users/{$secondOwner->id}/deactivate")->assertForbidden();
    }
}
