<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Notifications\QueuedAppNotice;
use App\Services\Tenant\TenantContext;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * A customer with no email has no way to reset a password alone, and a new
 * client has no way to make a setup link. Both can ask the business from the
 * booking page, and the team is told.
 */
class LoginLinkRequestTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private string $reply = 'Thank you. If Test Salon has those details for you, they have been asked to send your login link. They usually reply during business hours.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
        $this->tenant = Tenant::create(['name' => 'Test Salon', 'slug' => 'test-salon', 'is_active' => true, 'phone' => '0821234567']);
        $this->tenant->activateModule('booking');
        Notification::fake();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function customer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'tenant_id' => $this->tenant->id, 'name' => 'Thandi', 'phone' => '082 999 8888', 'is_active' => true,
        ], $overrides));
    }

    public function test_the_claim_and_forgot_password_pages_offer_the_request_form(): void
    {
        $this->get('/book/test-salon/claim')->assertOk()->assertSee('Ask Test Salon for my link')->assertSee('name="contact"', false);
        $this->get('/book/test-salon/forgot-password')->assertOk()->assertSee('Ask Test Salon for my link')->assertSee('name="contact"', false);
    }

    public function test_asking_with_a_cell_number_tells_the_team_and_links_to_the_profile(): void
    {
        $owner = $this->staff('tenant-owner');
        $employee = $this->staff('employee');
        $customer = $this->customer();
        TenantContext::clear();

        $this->post('/book/test-salon/claim/request', ['contact' => '+27 82 999 8888'])
            ->assertRedirect()->assertSessionHas('success', $this->reply);

        foreach ([$owner, $employee] as $member) {
            Notification::assertSentTo($member, QueuedAppNotice::class, fn ($n) => $n->title === 'Thandi asked for a login link'
                && str_contains($n->message, 'their login setup link')
                && $n->url === route('customers.show', $customer->id));
        }
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'customer.setup_link_requested')->where('entity_id', $customer->id)->count());
    }

    public function test_an_unknown_number_gets_the_same_reply_and_tells_nobody(): void
    {
        $this->staff('tenant-owner');
        $this->customer();
        TenantContext::clear();

        $this->post('/book/test-salon/claim/request', ['contact' => '0830000000'])
            ->assertRedirect()->assertSessionHas('success', $this->reply);

        Notification::assertNothingSent();
    }

    public function test_pressing_it_again_does_not_send_another_notice(): void
    {
        $owner = $this->staff('tenant-owner');
        $this->customer();
        TenantContext::clear();

        $this->post('/book/test-salon/claim/request', ['contact' => '0829998888']);
        $this->post('/book/test-salon/claim/request', ['contact' => '0829998888'])->assertSessionHas('success', $this->reply);

        Notification::assertSentToTimes($owner, QueuedAppNotice::class, 1);
    }

    public function test_a_request_from_someone_who_already_has_a_login_goes_to_managers_only(): void
    {
        $owner = $this->staff('tenant-owner');
        $employee = $this->staff('employee');
        $this->customer(['email' => 'thandi@example.com', 'password' => Hash::make('forgotten')]);
        TenantContext::clear();

        $this->post('/book/test-salon/claim/request', ['contact' => 'Thandi@Example.com'])->assertSessionHas('success', $this->reply);

        Notification::assertSentTo($owner, QueuedAppNotice::class, fn ($n) => str_contains($n->message, 'a manager or owner needs to create it'));
        Notification::assertNotSentTo($employee, QueuedAppNotice::class);
    }

    public function test_another_business_is_never_told(): void
    {
        $other = Tenant::create(['name' => 'Other Salon', 'slug' => 'other-salon', 'is_active' => true]);
        $otherOwner = User::factory()->create(['tenant_id' => $other->id, 'is_active' => true]);
        $otherOwner->assignRole('tenant-owner');
        $this->staff('tenant-owner');
        $this->customer();
        TenantContext::clear();

        $this->post('/book/other-salon/claim/request', ['contact' => '0829998888'])
            ->assertSessionHas('success', 'Thank you. If Other Salon has those details for you, they have been asked to send your login link. They usually reply during business hours.');

        Notification::assertNothingSent();
    }

    public function test_something_that_is_not_a_number_or_an_email_is_explained(): void
    {
        $this->post('/book/test-salon/claim/request', ['contact' => 'thandi'])
            ->assertSessionHasErrors(['contact' => 'Enter your 10-digit cell number, like 082 123 4567, or your email address.']);
        $this->post('/book/test-salon/claim/request', [])->assertSessionHasErrors('contact');

        Notification::assertNothingSent();
    }

    public function test_requests_from_one_network_are_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/book/test-salon/claim/request', ['contact' => "083000000{$i}"])->assertStatus(302);
        }

        $this->post('/book/test-salon/claim/request', ['contact' => '0830000009'])
            ->assertStatus(429)
            ->assertSee('You have asked a few times already');
    }

    public function test_setup_links_last_a_week(): void
    {
        $customer = $this->customer();
        $link = $customer->issueSetupLink();

        $this->assertSame(168, Customer::SETUP_LINK_HOURS);
        $this->travel(6)->days();
        $this->get($link)->assertOk();
        $this->travel(2)->days();
        $this->get($link)->assertRedirect(route('book.claim', 'test-salon'));
    }
}
