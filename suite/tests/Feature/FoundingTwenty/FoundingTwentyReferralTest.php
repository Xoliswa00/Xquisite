<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\PromoCodeRedemption;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FoundingTwentyReferralTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    private function tenant(string $name): Tenant
    {
        return Tenant::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'email' => strtolower(str_replace(' ', '', $name)) . '@example.com',
            'is_active' => true,
        ]);
    }

    private function application(array $overrides = []): FoundingTwentyApplication
    {
        return FoundingTwentyApplication::create(array_merge([
            'business_name' => 'Referred Salon', 'owner_name' => 'X', 'phone' => '0821234567',
            'business_type' => 'salon', 'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 1, 'pain_late_cancellations' => 1, 'pain_no_shows' => 1,
            'pain_double_bookings' => 1, 'pain_booking_enquiry_time' => 1, 'pain_staff_availability' => 1,
            'pain_tracking_balances' => 1, 'pain_revenue_visibility' => 1, 'pain_customer_data_organisation' => 1,
            'value_rating' => 1,
        ], $overrides));
    }

    public function test_reward_cannot_be_processed_without_a_referrer(): void
    {
        $newTenant = $this->tenant('New Business');
        $application = $this->application(['tenant_id' => $newTenant->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.founding-twenty.referral-reward', $application))
            ->assertStatus(422);
    }

    public function test_reward_cannot_be_processed_without_a_linked_tenant(): void
    {
        $referrer = $this->tenant('Referrer Business');
        $application = $this->application(['referred_by_tenant_id' => $referrer->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.founding-twenty.referral-reward', $application))
            ->assertStatus(422);
    }

    public function test_successful_reward_creates_both_redemptions_and_marks_processed(): void
    {
        $referrer = $this->tenant('Referrer Business');
        $newTenant = $this->tenant('New Business');
        $application = $this->application([
            'referred_by_tenant_id' => $referrer->id,
            'tenant_id' => $newTenant->id,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.founding-twenty.referral-reward', $application))
            ->assertRedirect();

        $this->assertNotNull($application->fresh()->referral_reward_processed_at);
        $this->assertSame(1, PromoCodeRedemption::where('tenant_id', $referrer->id)->count());
        $this->assertSame(1, PromoCodeRedemption::where('tenant_id', $newTenant->id)->count());
    }

    public function test_reward_cannot_be_processed_twice(): void
    {
        $referrer = $this->tenant('Referrer Business');
        $newTenant = $this->tenant('New Business');
        $application = $this->application([
            'referred_by_tenant_id' => $referrer->id,
            'tenant_id' => $newTenant->id,
            'referral_reward_processed_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.founding-twenty.referral-reward', $application))
            ->assertStatus(422);

        $this->assertSame(0, PromoCodeRedemption::count());
    }
}
