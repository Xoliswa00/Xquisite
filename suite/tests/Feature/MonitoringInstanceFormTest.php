<?php

namespace Tests\Feature;

use App\Models\MonitoredInstance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The /monitoring create + edit forms now capture `slug` and correctly map the
 * `active` checkbox onto the `is_active` column (previously dropped).
 */
class MonitoringInstanceFormTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    public function test_store_requires_slug_and_persists_is_active_false(): void
    {
        $this->actingAs($this->admin())
            ->post('/monitoring', [
                'name'      => 'Keystone',
                'slug'      => 'keystone',
                'url'       => 'https://keystone.test/api/health',
                'api_token' => str_repeat('a', 48),
                // 'active' checkbox unchecked -> absent
            ])
            ->assertRedirect();

        $instance = MonitoredInstance::first();
        $this->assertSame('keystone', $instance->slug);
        $this->assertFalse($instance->is_active);
    }

    public function test_store_rejects_missing_slug(): void
    {
        $this->actingAs($this->admin())
            ->post('/monitoring', [
                'name'      => 'No Slug',
                'url'       => 'https://x.test/api/health',
                'api_token' => str_repeat('b', 48),
                'active'    => '1',
            ])
            ->assertSessionHasErrors('slug');

        $this->assertSame(0, MonitoredInstance::count());
    }

    public function test_store_sets_is_active_true_when_checked(): void
    {
        $this->actingAs($this->admin())
            ->post('/monitoring', [
                'name'      => 'Active One',
                'slug'      => 'active-one',
                'url'       => 'https://a.test/api/health',
                'api_token' => str_repeat('c', 48),
                'active'    => '1',
            ])
            ->assertRedirect();

        $this->assertTrue(MonitoredInstance::first()->is_active);
    }
}
