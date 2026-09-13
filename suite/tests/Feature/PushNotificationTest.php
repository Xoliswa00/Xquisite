<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentReminder;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Notifications\AppNotice;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\WebPush\PushSubscription;
use NotificationChannels\WebPush\WebPushChannel;
use Tests\TestCase;

/**
 * Covers the push-notification rollout: the subscribe/unsubscribe endpoints
 * for both notifiable types (staff User, booking Customer), AppNotice's
 * conditional webpush channel (added to the SendsWebPush trait shared by
 * every notification class in the app), and the reminder command actually
 * notifying the customer, not just emailing them.
 */
class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(): Tenant
    {
        $tenant = Tenant::create([
            'name'      => 'Test Salon',
            'slug'      => 'test-salon-' . uniqid(),
            'email'     => 'salon@example.com',
            'is_active' => true,
        ]);
        TenantContext::set($tenant->id);

        return $tenant;
    }

    private function subscriptionPayload(string $endpoint = 'https://fcm.googleapis.com/fcm/send/test-endpoint'): array
    {
        return [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => str_repeat('a', 20),
                'auth'   => str_repeat('b', 10),
            ],
        ];
    }

    public function test_staff_user_can_subscribe_and_unsubscribe_for_push(): void
    {
        $tenant = $this->makeTenant();
        $user   = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->postJson(route('push.subscribe'), $this->subscriptionPayload())
            ->assertOk()
            ->assertJson(['subscribed' => true]);

        $this->assertSame(1, $user->pushSubscriptions()->count());

        $this->actingAs($user)
            ->deleteJson(route('push.unsubscribe'), ['endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint'])
            ->assertOk()
            ->assertJson(['unsubscribed' => true]);

        $this->assertSame(0, $user->pushSubscriptions()->count());
    }

    public function test_customer_can_subscribe_for_push_on_their_own_tenant_slug(): void
    {
        $tenant   = $this->makeTenant();
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Jane Client',
            'email'     => 'jane@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($customer, 'customer')
            ->postJson(route('book.push.subscribe', $tenant->slug), $this->subscriptionPayload())
            ->assertOk();

        $this->assertSame(1, $customer->pushSubscriptions()->count());
        $this->assertSame(0, PushSubscription::where('subscribable_type', User::class)->count());
    }

    public function test_subscribe_endpoint_requires_authentication(): void
    {
        $this->postJson(route('push.subscribe'), $this->subscriptionPayload())
            ->assertUnauthorized();
    }

    public function test_app_notice_only_uses_webpush_channel_when_a_subscription_exists(): void
    {
        $tenant = $this->makeTenant();
        $user   = User::factory()->create(['tenant_id' => $tenant->id]);

        $notice = new AppNotice(title: 'Test', message: 'Body', url: null);

        $this->assertNotContains(WebPushChannel::class, $notice->via($user));

        $user->updatePushSubscription('https://fcm.googleapis.com/fcm/send/x', 'key', 'token');

        $this->assertContains(WebPushChannel::class, $notice->via($user->fresh()));
    }

    public function test_reminder_command_notifies_the_customer_in_app_in_addition_to_emailing_them(): void
    {
        Mail::fake();
        Notification::fake();

        $tenant   = $this->makeTenant();
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Jane Client',
            'email'     => 'jane@example.com',
            'is_active' => true,
        ]);
        $service = Service::create([
            'tenant_id'        => $tenant->id,
            'name'             => 'Haircut',
            'duration_minutes' => 60,
            'price'            => 250,
            'pricing_type'     => 'flat',
            'is_active'        => true,
        ]);

        $appt = Appointment::create([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'scheduled_at'     => Carbon::now()->addDays(2),
            'duration_minutes' => 60,
            'status'           => 'confirmed',
        ]);
        $appt->services()->attach($service->id, ['duration_minutes' => 60, 'price_at_booking' => 250]);

        // The observer already scheduled reminders on create — force one due now
        // rather than depend on the exact 24h/1h timing from the earlier test.
        AppointmentReminder::where('appointment_id', $appt->id)->update(['scheduled_at' => Carbon::now()->subMinute()]);

        Artisan::call('booking:send-reminders');

        Notification::assertSentTo($customer, AppNotice::class, function (AppNotice $notice) {
            return $notice->title === 'Appointment reminder';
        });
    }
}
