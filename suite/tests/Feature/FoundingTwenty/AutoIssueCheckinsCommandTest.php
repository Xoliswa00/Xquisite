<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoIssueCheckinsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant-' . uniqid(),
            'email' => 'tenant@example.com',
            'is_active' => true,
        ]);
    }

    private function application(array $overrides = []): FoundingTwentyApplication
    {
        return FoundingTwentyApplication::create(array_merge([
            'business_name' => 'Test Salon', 'owner_name' => 'X', 'phone' => '082123' . rand(1000, 9999),
            'business_type' => 'salon', 'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 1, 'pain_late_cancellations' => 1, 'pain_no_shows' => 1,
            'pain_double_bookings' => 1, 'pain_booking_enquiry_time' => 1, 'pain_staff_availability' => 1,
            'pain_tracking_balances' => 1, 'pain_revenue_visibility' => 1, 'pain_customer_data_organisation' => 1,
            'value_rating' => 1,
        ], $overrides));
    }

    public function test_issues_30_day_checkin_once_30_days_have_passed(): void
    {
        $application = $this->application(['tenant_id' => $this->tenant()->id, 'tenant_linked_at' => now()->subDays(31)]);

        $this->artisan('founding-twenty:auto-issue-checkins')->assertExitCode(0);

        $this->assertSame(1, $application->checkins()->where('checkin_type', '30_day')->count());
        $this->assertSame(0, $application->checkins()->where('checkin_type', '60_day')->count());
    }

    public function test_does_not_issue_early(): void
    {
        $application = $this->application(['tenant_id' => $this->tenant()->id, 'tenant_linked_at' => now()->subDays(10)]);

        $this->artisan('founding-twenty:auto-issue-checkins');

        $this->assertSame(0, $application->checkins()->count());
    }

    public function test_does_not_duplicate_an_already_issued_checkin(): void
    {
        $application = $this->application(['tenant_id' => $this->tenant()->id, 'tenant_linked_at' => now()->subDays(31)]);
        $application->checkins()->create(['checkin_type' => '30_day']);

        $this->artisan('founding-twenty:auto-issue-checkins');

        $this->assertSame(1, $application->checkins()->where('checkin_type', '30_day')->count());
    }

    public function test_issues_multiple_thresholds_at_once_after_a_gap(): void
    {
        $application = $this->application(['tenant_id' => $this->tenant()->id, 'tenant_linked_at' => now()->subDays(95)]);

        $this->artisan('founding-twenty:auto-issue-checkins');

        $this->assertSame(3, $application->checkins()->count());
    }

    public function test_ignores_applications_with_no_tenant_linked_at(): void
    {
        $this->application(['tenant_id' => null, 'tenant_linked_at' => null]);

        $this->artisan('founding-twenty:auto-issue-checkins');

        $this->assertSame(0, \App\Models\FoundingTwentyCheckin::count());
    }
}
