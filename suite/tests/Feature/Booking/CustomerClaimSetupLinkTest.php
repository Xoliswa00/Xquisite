<?php

namespace Tests\Feature\Booking;

use App\Http\Controllers\Booking\CustomerAuthController;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Services\Tenant\TenantContext;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * A manually-added customer used to be able to "claim" their record by typing
 * its phone number, which let anyone who knew the number set the password.
 * The only way in now is a signed link the business sends them.
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
            'phone'     => '0829998888',
            'is_active' => true,
        ], $overrides));
    }

    public function test_a_phone_number_alone_can_no_longer_claim_an_account(): void
    {
        $customer = $this->customer();

        $this->post('/book/test-salon/claim', ['phone' => '0829998888'])->assertStatus(405);

        // The old session-based setup step is gone too.
        $this->withSession(['claim_customer_id' => $customer->id])
            ->post('/book/test-salon/claim/setup', ['email' => 'attacker@example.com', 'password' => 'password1', 'password_confirmation' => 'password1'])
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

    public function test_an_email_that_already_has_a_login_gets_a_helpful_message(): void
    {
        $this->customer(['name' => 'Lerato', 'phone' => '0827776666', 'email' => 'taken@example.com', 'password' => Hash::make('password')]);
        $customer = $this->customer();

        $this->post(CustomerAuthController::claimSetupUrl($customer, 'test-salon'), [
            'email' => 'taken@example.com', 'password' => 'my-own-password', 'password_confirmation' => 'my-own-password',
        ])->assertSessionHasErrors(['email' => 'This email already has a login. Sign in or reset your password, or use a different email here.']);

        $this->assertNull($customer->fresh()->password);
    }

    public function test_the_setup_page_needs_a_valid_signature(): void
    {
        $customer = $this->customer();

        $this->get("/book/test-salon/claim/setup/{$customer->id}")
            ->assertRedirect(route('book.claim', 'test-salon'))
            ->assertSessionHasErrors('link');

        $this->post("/book/test-salon/claim/setup/{$customer->id}", ['email' => 'attacker@example.com', 'password' => 'password1', 'password_confirmation' => 'password1'])
            ->assertRedirect(route('book.claim', 'test-salon'));

        // A real link for one customer cannot be pointed at another.
        $other = $this->customer(['name' => 'Lerato', 'phone' => '0827776666']);
        $tampered = str_replace("/setup/{$customer->id}", "/setup/{$other->id}", CustomerAuthController::claimSetupUrl($customer, 'test-salon'));
        $this->get($tampered)->assertRedirect(route('book.claim', 'test-salon'));

        $this->assertNull($customer->fresh()->password);
        $this->assertNull($other->fresh()->password);
    }

    public function test_the_signed_link_sets_up_the_login_once(): void
    {
        $customer = $this->customer();
        $link = CustomerAuthController::claimSetupUrl($customer, 'test-salon');

        $this->get($link)->assertOk()->assertSee('Thandi')->assertSee('Activate my account');

        $this->post($link, ['email' => 'thandi@example.com', 'password' => 'my-own-password', 'password_confirmation' => 'my-own-password'])
            ->assertRedirect(route('book.index', 'test-salon'));

        $this->assertAuthenticatedAs($customer->fresh(), 'customer');
        $this->assertSame('thandi@example.com', $customer->fresh()->email);
        $this->assertTrue(Hash::check('my-own-password', $customer->fresh()->password));
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'customer.claim_completed')->where('entity_id', $customer->id)->count());

        // The same link is dead once a password exists.
        auth('customer')->logout();
        $this->get($link)->assertRedirect(route('book.login', 'test-salon'));
        $this->post($link, ['email' => 'attacker@example.com', 'password' => 'stolen-password', 'password_confirmation' => 'stolen-password'])
            ->assertRedirect(route('book.login', 'test-salon'));

        $this->assertSame('thandi@example.com', $customer->fresh()->email);
        $this->assertTrue(Hash::check('my-own-password', $customer->fresh()->password));
    }

    public function test_the_link_expires(): void
    {
        $customer = $this->customer();
        $link = CustomerAuthController::claimSetupUrl($customer, 'test-salon');

        $this->travel(CustomerAuthController::CLAIM_LINK_DAYS)->days();
        $this->travel(1)->minutes();

        $this->get($link)
            ->assertRedirect(route('book.claim', 'test-salon'))
            ->assertSessionHasErrors('link');
    }

    public function test_a_link_does_not_work_under_another_business(): void
    {
        $other = Tenant::create(['name' => 'Other Salon', 'slug' => 'other-salon', 'is_active' => true]);
        $customer = $this->customer();

        // Even a correctly signed link for the wrong business is refused.
        $this->get(CustomerAuthController::claimSetupUrl($customer, 'other-salon'))->assertNotFound();
        $this->assertNull($customer->fresh()->password);
    }

    public function test_staff_see_the_setup_link_only_for_customers_without_a_login(): void
    {
        $this->seed(PermissionRoleSeeder::class);
        $this->tenant->activateModule('booking');
        $staff = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        $staff->assignRole('employee');

        $noLogin = $this->customer();
        $hasLogin = $this->customer(['name' => 'Lerato', 'phone' => '0827776666', 'email' => 'lerato@example.com', 'password' => Hash::make('password')]);

        TenantContext::clear();

        $this->actingAs($staff)->get(route('customers.show', $noLogin))
            ->assertOk()
            ->assertSee('No online login yet')
            ->assertSee('Send on WhatsApp')
            ->assertSee(rawurlencode('We will never ask you for your password.'), false)
            ->assertSee("/book/test-salon/claim/setup/{$noLogin->id}", false)
            ->assertSee('signature=', false);

        $this->actingAs($staff)->get(route('customers.show', $hasLogin))
            ->assertOk()
            ->assertDontSee('No online login yet');
    }
}
