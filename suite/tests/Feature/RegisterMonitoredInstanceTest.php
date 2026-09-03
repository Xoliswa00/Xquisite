<?php

namespace Tests\Feature;

use App\Models\MonitoredInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * php artisan monitoring:register-instance — CLI reporter registration.
 * See docs/MONITORING_INGEST.md.
 */
class RegisterMonitoredInstanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_new_active_instance_with_a_token(): void
    {
        $this->artisan('monitoring:register-instance', [
            'slug'   => 'nobela',
            '--name' => 'Nobela Enterprises',
            '--url'  => 'https://nobela.test/api/health',
        ])->assertSuccessful();

        $instance = MonitoredInstance::where('slug', 'nobela')->first();

        $this->assertNotNull($instance);
        $this->assertSame('Nobela Enterprises', $instance->name);
        $this->assertTrue($instance->is_active);
        $this->assertGreaterThanOrEqual(40, strlen($instance->api_token));
    }

    public function test_it_defaults_the_name_from_the_slug(): void
    {
        $this->artisan('monitoring:register-instance', [
            'slug'  => 'bx-eventos',
            '--url' => 'https://bx.test/api/health',
        ])->assertSuccessful();

        $this->assertSame('Bx Eventos', MonitoredInstance::where('slug', 'bx-eventos')->value('name'));
    }

    public function test_re_running_is_idempotent_and_keeps_the_token(): void
    {
        $this->artisan('monitoring:register-instance', [
            'slug'  => 'nobela',
            '--url' => 'https://nobela.test/api/health',
        ])->assertSuccessful();

        $token = MonitoredInstance::where('slug', 'nobela')->value('api_token');

        $this->artisan('monitoring:register-instance', [
            'slug'  => 'nobela',
            '--url' => 'https://nobela.test/api/health',
        ])->assertSuccessful();

        $this->assertSame(1, MonitoredInstance::where('slug', 'nobela')->count());
        $this->assertSame($token, MonitoredInstance::where('slug', 'nobela')->value('api_token'));
    }

    public function test_rotate_token_issues_a_new_one(): void
    {
        $this->artisan('monitoring:register-instance', ['slug' => 'nobela', '--url' => 'https://nobela.test/api/health'])->assertSuccessful();
        $old = MonitoredInstance::where('slug', 'nobela')->value('api_token');

        $this->artisan('monitoring:register-instance', ['slug' => 'nobela', '--url' => 'https://nobela.test/api/health', '--rotate-token' => true])->assertSuccessful();

        $this->assertNotSame($old, MonitoredInstance::where('slug', 'nobela')->value('api_token'));
    }

    public function test_it_repairs_a_legacy_row_that_has_no_slug(): void
    {
        $legacy = MonitoredInstance::create([
            'name'      => 'Nobela Enterprises',
            'url'       => 'https://nobela.test/api/health',
            'api_token' => str_repeat('a', 48),
            'status'    => 'unknown',
            'is_active' => false,
        ]);

        $this->artisan('monitoring:register-instance', [
            'slug'   => 'nobela',
            '--name' => 'Nobela Enterprises',
            '--url'  => 'https://nobela.test/api/health',
        ])->assertSuccessful();

        $legacy->refresh();
        $this->assertSame('nobela', $legacy->slug);
        $this->assertTrue($legacy->is_active);
        $this->assertSame(str_repeat('a', 48), $legacy->api_token);
        $this->assertSame(1, MonitoredInstance::count());
    }

    public function test_deactivate_flips_is_active(): void
    {
        $this->artisan('monitoring:register-instance', ['slug' => 'nobela', '--url' => 'https://nobela.test/api/health'])->assertSuccessful();

        $this->artisan('monitoring:register-instance', ['slug' => 'nobela', '--deactivate' => true])->assertSuccessful();

        $this->assertFalse((bool) MonitoredInstance::where('slug', 'nobela')->value('is_active'));
    }

    public function test_it_rejects_an_invalid_slug(): void
    {
        $this->artisan('monitoring:register-instance', ['slug' => 'Nobela Enterprises', '--url' => 'https://x.test/api/health'])
            ->assertFailed();

        $this->assertSame(0, MonitoredInstance::count());
    }

    public function test_a_brand_new_instance_requires_a_url(): void
    {
        $this->artisan('monitoring:register-instance', ['slug' => 'nobela'])->assertFailed();

        $this->assertSame(0, MonitoredInstance::count());
    }
}
