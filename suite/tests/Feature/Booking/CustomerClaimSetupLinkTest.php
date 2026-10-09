<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Notifications\CustomerLoginReplacedNotification;
use App\Services\Tenant\TenantContext;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * A manually-added customer used to be able to "claim" their record by typing
 * its phone number, which let anyone who knew the number set the password.
 * The only way in now is a signed link that staff create on purpose and send,
 * which works once, for a short time, and can be cancelled.
 */
class CustomerClaimSetupLinkTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Test Salon', 'slug' => 'test-salon', 'is_active' => true, 'phone' => '0821234567']);
    }

    private function customer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Thandi',
            'phone'     => '082 999 8888',
            'is_active' => true,
        ], $overrides));
    }

    private function staff(string $role = 'employee'): User
    {
        $this->seed(PermissionRoleSeeder::class);
        $this->tenant->activateModule('booking');
        $staff = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        $staff->assignRole($role);
        TenantContext::clear();

        return $staff;
    }

    private array $newPassword = ['password' => 'my-own-password', 'password_confirmation' => 'my-own-password'];

    // ── The old way in is closed ────────────────────────────────────────────

    public function test_a_phone_number_alone_can_no_longer_claim_an_account(): void
    {
        $customer = $this->customer();

        $this->post('/book/test-salon/claim', ['phone' => '0829998888'])->assertStatus(405);
        $this->withSession(['claim_customer_id' => $customer->id])
            ->post('/book/test-salon/claim/setup', ['email' => 'attacker@example.com'] + $this->newPassword)
            ->assertNotFound();

        $this->assertNull($customer->fresh()->password);
        $this->assertGuest('customer');
    }

    public function test_the_claim_page_explains_how_to_get_a_link_and_has_no_lookup_form(): void
    {
        $this->get('/book/test-salon/claim')
            ->assertOk()
            ->assertSee('Ask Test Salon for your setup link')
            ->assertSee('My%20name%20is', false)
            ->assertSee('wa.me/27821234567', false)
            ->assertDontSee('name="phone"', false);
    }

    public function test_the_claim_page_still_gives_a_next_step_when_the_business_has_no_phone(): void
    {
        $this->tenant->update(['phone' => null]);

        $this->get('/book/test-salon/claim')
            ->assertOk()
            ->assertDontSee('wa.me', false)
            ->assertSee('at your next visit');
    }

    // ── A link exists only when staff make one ──────────────────────────────

    public function test_opening_a_profile_does_not_create_a_link(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();

        $this->actingAs($staff)->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('No online login yet')
            ->assertSee('Create setup link')
            ->assertDontSee('claim/setup', false);

        $this->assertSame(0, $customer->fresh()->setup_link_version);
        $this->assertNull($customer->fresh()->setup_link_expires_at);
    }

    public function test_staff_create_a_link_and_it_is_logged(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();

        $this->actingAs($staff)->post(route('customers.setup-link.store', $customer))
            ->assertRedirect()->assertSessionHas('success');

        $customer->refresh();
        $this->assertTrue($customer->hasActiveSetupLink());
        $this->assertEqualsWithDelta(now()->addHours(Customer::SETUP_LINK_HOURS)->getTimestamp(), $customer->setup_link_expires_at->getTimestamp(), 5);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'customer.setup_link_created')->where('entity_id', $customer->id)->where('user_id', $staff->id)->count());

        TenantContext::clear();
        $this->actingAs($staff)->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Setup link ready')
            ->assertSee('Send on WhatsApp')
            ->assertSee('Cancel link')
            ->assertSee("/book/test-salon/claim/setup/{$customer->id}", false)
            ->assertSee(rawurlencode('We will never ask you for your password.'), false);
    }

    public function test_the_same_link_is_shown_again_without_making_a_new_one(): void
    {
        $customer = $this->customer();
        $first = $customer->issueSetupLink();

        $this->assertSame($first, $customer->fresh()->setupLinkUrl());
        $this->assertSame(1, $customer->fresh()->setup_link_version);
    }

    // ── Using the link ──────────────────────────────────────────────────────

    public function test_the_setup_page_needs_a_valid_signature(): void
    {
        $customer = $this->customer();
        $other = $this->customer(['name' => 'Lerato', 'phone' => '0827776666']);
        $link = $customer->issueSetupLink();
        $other->issueSetupLink();

        $this->get("/book/test-salon/claim/setup/{$customer->id}?v=1")
            ->assertRedirect(route('book.claim', 'test-salon'))
            ->assertSessionHasErrors('link');
        $this->post("/book/test-salon/claim/setup/{$customer->id}?v=1", ['email' => 'attacker@example.com'] + $this->newPassword)
            ->assertRedirect(route('book.claim', 'test-salon'));

        // A real link for one customer cannot be pointed at another.
        $this->get(str_replace("/setup/{$customer->id}", "/setup/{$other->id}", $link))
            ->assertRedirect(route('book.claim', 'test-salon'));

        $this->assertNull($customer->fresh()->password);
        $this->assertNull($other->fresh()->password);
    }

    public function test_a_customer_with_a_cell_number_can_finish_without_an_email(): void
    {
        $customer = $this->customer();
        $link = $customer->issueSetupLink();

        $this->get($link)->assertOk()->assertSee('Thandi')->assertSee('(optional)')->assertSee('Save my login');

        $this->post($link, $this->newPassword)->assertRedirect(route('book.index', 'test-salon'));

        $customer->refresh();
        $this->assertAuthenticatedAs($customer, 'customer');
        $this->assertNull($customer->email);
        $this->assertTrue(Hash::check('my-own-password', $customer->password));
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'customer.claim_completed')->where('entity_id', $customer->id)->count());
    }

    public function test_a_customer_with_no_cell_number_must_give_an_email(): void
    {
        $customer = $this->customer(['phone' => null]);
        $link = $customer->issueSetupLink();

        $this->post($link, $this->newPassword)->assertSessionHasErrors('email');
        $this->assertNull($customer->fresh()->password);

        $this->post($link, ['email' => 'thandi@example.com'] + $this->newPassword)
            ->assertRedirect(route('book.index', 'test-salon'));
        $this->assertSame('thandi@example.com', $customer->fresh()->email);
    }

    public function test_the_link_works_once(): void
    {
        $customer = $this->customer();
        $link = $customer->issueSetupLink();

        $this->post($link, ['email' => 'thandi@example.com'] + $this->newPassword);
        auth('customer')->logout();

        $this->get($link)->assertRedirect(route('book.login', 'test-salon'));
        $this->post($link, ['email' => 'attacker@example.com', 'password' => 'stolen-password', 'password_confirmation' => 'stolen-password'])
            ->assertRedirect(route('book.login', 'test-salon'));

        $this->assertSame('thandi@example.com', $customer->fresh()->email);
        $this->assertTrue(Hash::check('my-own-password', $customer->fresh()->password));
        $this->assertFalse($customer->fresh()->hasActiveSetupLink());
    }

    public function test_the_link_expires(): void
    {
        $customer = $this->customer();
        $link = $customer->issueSetupLink();

        $this->travel(Customer::SETUP_LINK_HOURS)->hours();
        $this->travel(1)->minutes();

        $this->get($link)
            ->assertRedirect(route('book.claim', 'test-salon'))
            ->assertSessionHasErrors('link');
        $this->post($link, $this->newPassword)->assertRedirect(route('book.claim', 'test-salon'));
        $this->assertNull($customer->fresh()->password);
    }

    public function test_staff_can_cancel_a_link(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();
        $link = $customer->issueSetupLink();

        $this->actingAs($staff)->delete(route('customers.setup-link.destroy', $customer))
            ->assertRedirect()->assertSessionHas('success');
        auth()->logout();
        TenantContext::clear();

        $this->get($link)->assertRedirect(route('book.claim', 'test-salon'))->assertSessionHasErrors('link');
        $this->post($link, $this->newPassword)->assertRedirect(route('book.claim', 'test-salon'));

        $this->assertNull($customer->fresh()->password);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'customer.setup_link_cancelled')->count());
    }

    public function test_a_new_link_cancels_the_one_before_it(): void
    {
        $customer = $this->customer();
        $old = $customer->issueSetupLink();
        $new = $customer->issueSetupLink();

        $this->assertNotSame($old, $new);
        $this->get($old)->assertRedirect(route('book.claim', 'test-salon'));
        $this->get($new)->assertOk();
    }

    public function test_a_link_does_not_work_under_another_business(): void
    {
        Tenant::create(['name' => 'Other Salon', 'slug' => 'other-salon', 'is_active' => true]);
        $customer = $this->customer();
        $link = $customer->issueSetupLink();

        // Changing the business in the address breaks the signature.
        $this->get(str_replace('/book/test-salon/', '/book/other-salon/', $link))
            ->assertRedirect(route('book.claim', 'other-salon'));
        $this->assertNull($customer->fresh()->password);
    }

    public function test_an_email_that_already_has_a_login_here_gets_a_helpful_message(): void
    {
        $this->customer(['name' => 'Lerato', 'phone' => '0827776666', 'email' => 'taken@example.com', 'password' => Hash::make('password')]);
        $customer = $this->customer();

        $this->post($customer->issueSetupLink(), ['email' => 'taken@example.com'] + $this->newPassword)
            ->assertSessionHasErrors(['email' => 'This email already has a login. Sign in or reset your password, or use a different email here.']);

        $this->assertNull($customer->fresh()->password);
    }

    // ── A forgotten password with no email on file ──────────────────────────

    public function test_a_manager_can_send_a_new_login_link_to_someone_who_already_has_a_login(): void
    {
        Notification::fake();
        $manager = $this->staff('manager');
        $customer = $this->customer(['email' => 'thandi@example.com', 'password' => Hash::make('forgotten-password')]);
        $customer->forceFill(['remember_token' => 'old-remember-token'])->save();

        $this->actingAs($manager)->get(route('customers.show', $customer))
            ->assertSee('Has an online login')
            ->assertSee('Create new login link')
            ->assertSee('Their current password keeps working until they use it');

        $this->actingAs($manager)->post(route('customers.setup-link.store', $customer));
        auth()->logout();
        TenantContext::clear();

        $link = $customer->fresh()->setupLinkUrl();
        $this->get($link)->assertOk()->assertSee('Choose a new password')->assertDontSee('name="email"', false);

        // The holder of the link can change the password, not move the login to another address.
        $this->post($link, ['email' => 'someone-else@example.com'] + $this->newPassword)
            ->assertRedirect(route('book.index', 'test-salon'));

        $customer->refresh();
        $this->assertSame('thandi@example.com', $customer->email);
        $this->assertTrue(Hash::check('my-own-password', $customer->password));
        $this->assertNotSame('old-remember-token', $customer->remember_token);
        Notification::assertSentTo($customer, CustomerLoginReplacedNotification::class);
    }

    public function test_a_team_member_cannot_replace_the_login_of_someone_who_already_has_one(): void
    {
        $employee = $this->staff('employee');
        $customer = $this->customer(['password' => Hash::make('their-password')]);

        $this->actingAs($employee)->get(route('customers.show', $customer))
            ->assertSee('Ask a manager to send a new login link')
            ->assertDontSee('Create new login link');

        $this->actingAs($employee)->post(route('customers.setup-link.store', $customer))->assertForbidden();
        $this->assertFalse($customer->fresh()->hasActiveSetupLink());
    }

    public function test_a_first_time_setup_does_not_send_the_password_changed_email(): void
    {
        Notification::fake();
        $customer = $this->customer();

        $this->post($customer->issueSetupLink(), ['email' => 'thandi@example.com'] + $this->newPassword);

        Notification::assertNothingSent();
    }

    public function test_a_second_login_on_the_same_cell_number_must_have_an_email(): void
    {
        $this->customer(['name' => 'Mother', 'password' => Hash::make('mothers-password')]);
        $daughter = $this->customer(['name' => 'Daughter']);
        $link = $daughter->issueSetupLink();

        $this->get($link)->assertOk()->assertDontSee('(optional)')->assertSee('Someone else already signs in with your cell number');

        $this->post($link, $this->newPassword)
            ->assertSessionHasErrors(['email' => 'Someone else already signs in with this cell number, so please add an email address to sign in with.']);
        $this->assertNull($daughter->fresh()->password);

        $this->post($link, ['email' => 'daughter@example.com'] + $this->newPassword)
            ->assertRedirect(route('book.index', 'test-salon'))
            ->assertSessionHas('success', 'Welcome, Daughter! Your login is ready. Next time, sign in with your email address.');
    }

    public function test_a_cancelled_link_for_someone_with_a_login_sends_them_to_sign_in_with_a_clear_message(): void
    {
        $customer = $this->customer(['password' => Hash::make('their-password')]);
        $link = $customer->issueSetupLink();
        $customer->cancelSetupLink();

        $this->get($link)
            ->assertRedirect(route('book.login', 'test-salon'))
            ->assertSessionHasErrors(['link' => 'That link no longer works. Ask Test Salon to send you a new one.']);
    }

    public function test_creating_a_link_from_the_list_returns_to_that_customer(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();

        $this->actingAs($staff)->from(route('customers.index', ['login' => 'none', 'page' => 2]))
            ->post(route('customers.setup-link.store', $customer))
            ->assertRedirect(route('customers.index', ['login' => 'none', 'page' => 2]) . '#customer-' . $customer->id);
    }

    // ── Working through the list ────────────────────────────────────────────

    public function test_the_customer_list_can_show_only_people_without_a_login(): void
    {
        $staff = $this->staff();
        $this->customer(['name' => 'Needs Link']);
        $this->customer(['name' => 'Already Set', 'phone' => '0827776666', 'password' => Hash::make('password')]);
        $ready = $this->customer(['name' => 'Link Waiting', 'phone' => '0825554444']);
        $ready->issueSetupLink();
        TenantContext::clear();

        $this->actingAs($staff)->get(route('customers.index', ['login' => 'none']))
            ->assertOk()
            ->assertSee('Needs Link')
            ->assertSee('Link Waiting')
            ->assertDontSee('Already Set')
            ->assertSee('Create setup link')
            ->assertSee('Send on WhatsApp')
            ->assertSee('2 customers with no online login')
            ->assertSee("/book/test-salon/claim/setup/{$ready->id}", false);

        $this->actingAs($staff)->get(route('customers.index', ['login' => 'has']))
            ->assertSee('Already Set')
            ->assertDontSee('Needs Link');
    }

    public function test_only_staff_who_manage_customers_can_create_or_cancel_links(): void
    {
        $customer = $this->customer();

        $this->post(route('customers.setup-link.store', $customer))->assertRedirect(route('login'));
        $this->delete(route('customers.setup-link.destroy', $customer))->assertRedirect(route('login'));

        $this->assertFalse($customer->fresh()->hasActiveSetupLink());
    }
}
