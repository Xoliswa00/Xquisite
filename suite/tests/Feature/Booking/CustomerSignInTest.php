<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Modules\Booking\Models\Customer;
use App\Services\Security\LoginThrottleService;
use App\Services\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Many clients have no email address, so a cell number works as the sign-in
 * name too. And a client of two businesses can have a login at each.
 */
class CustomerSignInTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::create(['name' => 'Test Salon', 'slug' => 'test-salon', 'is_active' => true]);
    }

    private function customer(array $overrides = [], ?Tenant $tenant = null): Customer
    {
        return Customer::create(array_merge([
            'tenant_id' => ($tenant ?? $this->tenant)->id,
            'name'      => 'Thandi',
            'email'     => 'thandi@example.com',
            'phone'     => '082 999 8888',
            'password'  => Hash::make('password'),
            'is_active' => true,
        ], $overrides));
    }

    // ── Cell number or email ────────────────────────────────────────────────

    public function test_the_sign_in_page_asks_for_email_or_cell_number(): void
    {
        $this->get('/book/test-salon/login')
            ->assertOk()
            ->assertSee('Email or cell number')
            ->assertSee('name="login"', false);
    }

    public function test_a_customer_signs_in_with_their_email(): void
    {
        $customer = $this->customer();

        $this->post('/book/test-salon/login', ['login' => 'Thandi@Example.com', 'password' => 'password'])
            ->assertRedirect(route('book.index', 'test-salon'));

        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_a_customer_signs_in_with_their_cell_number_however_it_is_typed(): void
    {
        $customer = $this->customer(['email' => null]);

        foreach (['0829998888', '082 999 8888', '+27 82 999 8888', '27829998888'] as $typed) {
            $this->post('/book/test-salon/login', ['login' => $typed, 'password' => 'password'])
                ->assertRedirect(route('book.index', 'test-salon'));
            $this->assertAuthenticatedAs($customer, 'customer');

            auth('customer')->logout();
        }
    }

    public function test_an_older_sign_in_page_that_posts_email_still_works(): void
    {
        $customer = $this->customer();

        $this->post('/book/test-salon/login', ['email' => 'thandi@example.com', 'password' => 'password'])
            ->assertRedirect(route('book.index', 'test-salon'));

        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_two_people_sharing_a_cell_number_each_get_their_own_account(): void
    {
        $mother = $this->customer(['name' => 'Mother', 'email' => null, 'password' => Hash::make('mothers-password')]);
        $daughter = $this->customer(['name' => 'Daughter', 'email' => null, 'password' => Hash::make('daughters-password')]);

        $this->post('/book/test-salon/login', ['login' => '0829998888', 'password' => 'daughters-password']);
        $this->assertAuthenticatedAs($daughter, 'customer');
        auth('customer')->logout();

        $this->post('/book/test-salon/login', ['login' => '0829998888', 'password' => 'mothers-password']);
        $this->assertAuthenticatedAs($mother, 'customer');
    }

    public function test_a_shared_cell_number_with_the_same_password_never_guesses_which_person(): void
    {
        $this->customer(['name' => 'Mother', 'email' => null]);
        $this->customer(['name' => 'Daughter', 'email' => 'daughter@example.com']);

        $this->post('/book/test-salon/login', ['login' => '0829998888', 'password' => 'password'])
            ->assertSessionHasErrors('login');
        $this->assertStringContainsString('More than one login uses this cell number and password', session('errors')->first('login'));
        $this->assertGuest('customer');

        // It is not counted as a wrong password, and the email still works.
        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'customer', 'login', '0829998888'));
        $this->post('/book/test-salon/login', ['login' => 'daughter@example.com', 'password' => 'password'])
            ->assertRedirect(route('book.index', 'test-salon'));
    }

    public function test_something_that_is_neither_an_email_nor_a_cell_number_is_not_counted_as_a_wrong_password(): void
    {
        $this->customer(['email' => null]);

        foreach (['82999888', '082x9998888', 'thandi', '021 555 12'] as $typed) {
            $this->post('/book/test-salon/login', ['login' => $typed, 'password' => 'password'])
                ->assertSessionHasErrors(['login' => 'Enter your email address or your 10-digit cell number, like 082 123 4567.']);
        }

        $this->assertNull(LoginThrottleService::lockedFor('127.0.0.1', 'customer', 'login', '82999888'));
        $this->post('/book/test-salon/login', ['login' => '0829998888', 'password' => 'password'])
            ->assertRedirect(route('book.index', 'test-salon'));
    }

    public function test_odd_input_is_refused_cleanly(): void
    {
        $this->post('/book/test-salon/login', ['email' => ['x'], 'password' => 'password'])
            ->assertStatus(302)->assertSessionHasErrors('login');
        $this->post('/book/test-salon/login', ['login' => ['x'], 'password' => 'password'])
            ->assertStatus(302)->assertSessionHasErrors('login');
        $this->post('/book/test-salon/login', ['password' => 'password'])
            ->assertStatus(302)->assertSessionHasErrors('login');
    }

    public function test_a_wrong_password_is_refused_the_same_way_for_known_and_unknown_numbers(): void
    {
        $this->customer(['email' => null]);

        $known = $this->post('/book/test-salon/login', ['login' => '0829998888', 'password' => 'wrong']);
        $unknown = $this->post('/book/test-salon/login', ['login' => '0820000000', 'password' => 'wrong']);

        $known->assertSessionHasErrors(['login' => 'These details do not match our records.']);
        $unknown->assertSessionHasErrors(['login' => 'These details do not match our records.']);
        $this->assertGuest('customer');
    }

    public function test_a_customer_with_no_password_cannot_sign_in(): void
    {
        $this->customer(['email' => null, 'password' => null]);

        $this->post('/book/test-salon/login', ['login' => '0829998888', 'password' => ''])->assertSessionHasErrors('password');
        $this->post('/book/test-salon/login', ['login' => '0829998888', 'password' => 'anything'])->assertSessionHasErrors('login');
        $this->assertGuest('customer');
    }

    public function test_three_wrong_passwords_pause_a_cell_number_sign_in_too(): void
    {
        $this->customer(['email' => null]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/book/test-salon/login', ['login' => '082 999 8888', 'password' => 'wrong']);
        }

        $this->post('/book/test-salon/login', ['login' => '082 999 8888', 'password' => 'password'])
            ->assertStatus(429)
            ->assertSee('Sign-in paused');
        $this->assertNotNull(LoginThrottleService::lockedFor('127.0.0.1', 'customer', 'login', '0829998888'));
    }

    public function test_writing_the_number_differently_does_not_earn_more_tries(): void
    {
        $this->customer(['email' => null]);

        foreach (['0829998888', '082 999 8888', '+27 82 999 8888'] as $typed) {
            $this->post('/book/test-salon/login', ['login' => $typed, 'password' => 'wrong'])->assertStatus(302);
        }

        // A fourth spelling, and the right password: still paused.
        $this->post('/book/test-salon/login', ['login' => '082-999-8888', 'password' => 'password'])
            ->assertStatus(429)
            ->assertSee('Sign-in paused');
        $this->assertGuest('customer');
    }

    public function test_sending_a_decoy_email_field_does_not_dodge_the_pause(): void
    {
        $this->customer();

        // The decoy is the name the pause check would read if it disagreed with the controller.
        for ($i = 0; $i < 3; $i++) {
            $this->post('/book/test-salon/login', ['email' => "decoy{$i}@example.com", 'login' => 'thandi@example.com', 'password' => 'wrong'])
                ->assertStatus(302);
        }

        $this->post('/book/test-salon/login', ['email' => 'another-decoy@example.com', 'login' => 'thandi@example.com', 'password' => 'password'])
            ->assertStatus(429);
        $this->assertGuest('customer');
    }

    public function test_the_stored_cell_number_form_follows_the_number(): void
    {
        $customer = $this->customer(['phone' => '+27 82 999 8888']);
        $this->assertSame('0829998888', $customer->fresh()->phone_normalised);

        $customer->update(['phone' => '083 111 2222']);
        $this->assertSame('0831112222', $customer->fresh()->phone_normalised);

        $customer->update(['phone' => null]);
        $this->assertNull($customer->fresh()->phone_normalised);

        $this->assertNull(Customer::normalisePhone('082x9998888'));
        $this->assertNull(Customer::normalisePhone('12345'));
        $this->assertSame('0821234567', Customer::normalisePhone('(082) 123-4567'));
    }

    public function test_a_password_reset_ends_remember_me_on_other_devices(): void
    {
        $customer = $this->customer();
        $customer->forceFill(['remember_token' => 'old-remember-token'])->save();
        TenantContext::set($this->tenant->id);
        $token = \Illuminate\Support\Facades\Password::broker('customers')->createToken($customer);
        TenantContext::clear();

        $this->post('/book/test-salon/reset-password', [
            'token' => $token, 'email' => 'thandi@example.com', 'password' => 'brand-new-password', 'password_confirmation' => 'brand-new-password',
        ])->assertRedirect(route('book.login', 'test-salon'));

        $this->assertNotSame('old-remember-token', $customer->fresh()->remember_token);
        $this->assertTrue(Hash::check('brand-new-password', $customer->fresh()->password));
    }

    // ── One email, more than one business ───────────────────────────────────

    public function test_a_customer_of_one_business_cannot_sign_in_at_another(): void
    {
        Tenant::create(['name' => 'Other Salon', 'slug' => 'other-salon', 'is_active' => true]);
        $this->customer();

        $this->post('/book/other-salon/login', ['login' => 'thandi@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('login');
        $this->post('/book/other-salon/login', ['login' => '0829998888', 'password' => 'password'])
            ->assertSessionHasErrors('login');
        $this->assertGuest('customer');
    }

    public function test_the_same_email_can_register_at_two_businesses_but_not_twice_at_one(): void
    {
        $other = Tenant::create(['name' => 'Other Salon', 'slug' => 'other-salon', 'is_active' => true]);
        $this->customer();
        TenantContext::clear();

        $form = ['name' => 'Thandi', 'email' => 'thandi@example.com', 'password' => 'another-password', 'password_confirmation' => 'another-password'];

        $this->post('/book/test-salon/register', $form)->assertSessionHasErrors('email');

        $this->post('/book/other-salon/register', $form)->assertRedirect(route('book.index', 'other-salon'));
        $this->assertSame(2, Customer::withoutGlobalScopes()->where('email', 'thandi@example.com')->count());
        $this->assertSame(1, Customer::withoutGlobalScopes()->where('email', 'thandi@example.com')->where('tenant_id', $other->id)->count());

        // Each login keeps its own password.
        auth('customer')->logout();
        $this->post('/book/test-salon/login', ['login' => 'thandi@example.com', 'password' => 'another-password'])->assertSessionHasErrors('login');
        $this->post('/book/test-salon/login', ['login' => 'thandi@example.com', 'password' => 'password'])->assertRedirect(route('book.index', 'test-salon'));
    }

    public function test_the_database_enforces_one_email_per_business(): void
    {
        $this->assertTrue(Schema::hasIndex('customers', 'customers_tenant_email_unique'));
        $this->assertFalse(Schema::hasIndex('customers', 'customers_email_unique'));
        $this->assertTrue(Schema::hasIndex('customers', 'customers_tenant_phone_idx'));

        $this->customer();
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $this->customer(['name' => 'Duplicate']);
    }
}
