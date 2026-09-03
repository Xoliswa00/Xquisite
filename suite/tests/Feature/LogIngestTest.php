<?php

namespace Tests\Feature;

use App\Models\MonitoredInstance;
use App\Notifications\CriticalLogAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * POST /ingest/logs — bearer-token log ingest from reporter apps into
 * system_logs. See docs/MONITORING_INGEST.md.
 */
class LogIngestTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstance(array $overrides = []): MonitoredInstance
    {
        return MonitoredInstance::create(array_merge([
            'name'      => 'Keystone',
            'slug'      => 'keystone',
            'url'       => 'https://keystone.test/api/health',
            'api_token' => 'tok_'.str_repeat('a', 44),
            'is_active' => true,
        ], $overrides));
    }

    private function event(array $overrides = []): array
    {
        return array_merge([
            'level'       => 'error',
            'message'     => 'RuntimeException: kaboom',
            'logged_at'   => now()->subMinute()->toIso8601String(),
            'fingerprint' => 'keystone-1',
        ], $overrides);
    }

    public function test_missing_token_is_401(): void
    {
        $this->postJson('/ingest/logs', ['events' => [$this->event()]])
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_bad_token_is_401(): void
    {
        $this->makeInstance();

        $this->withToken('nope')
            ->postJson('/ingest/logs', ['events' => [$this->event()]])
            ->assertStatus(401)
            ->assertJson(['error' => 'Invalid token']);
    }

    public function test_inactive_instance_is_401(): void
    {
        $i = $this->makeInstance(['is_active' => false]);

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [$this->event()]])
            ->assertStatus(401);
    }

    public function test_instance_without_slug_is_422(): void
    {
        $i = $this->makeInstance(['slug' => null]);

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [$this->event()]])
            ->assertStatus(422);
    }

    public function test_valid_batch_is_stored_with_source_and_uppercased_level(): void
    {
        $i = $this->makeInstance();

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [
                $this->event(['level' => 'error', 'fingerprint' => 'keystone-1']),
                $this->event(['level' => 'weird-level', 'fingerprint' => 'keystone-2']),
            ]])
            ->assertOk()
            ->assertJson(['accepted' => 2, 'duplicates' => 0, 'instance' => 'keystone']);

        $rows = DB::table('system_logs')->where('source', 'keystone')->get();
        $this->assertCount(2, $rows);
        $this->assertEqualsCanonicalizing(['ERROR', 'ERROR'], $rows->pluck('level')->all());
        $this->assertTrue($rows->every(fn ($r) => $r->status === 'new'));
        $this->assertTrue($rows->every(fn ($r) => $r->user_id === null));
        $this->assertTrue($rows->every(fn ($r) => ! empty($r->dedup_key)));
    }

    public function test_resending_a_batch_is_deduplicated(): void
    {
        $i = $this->makeInstance();
        $payload = ['events' => [$this->event(['fingerprint' => 'keystone-99'])]];

        $this->withToken($i->api_token)->postJson('/ingest/logs', $payload)->assertOk();
        $this->withToken($i->api_token)->postJson('/ingest/logs', $payload)
            ->assertOk()
            ->assertJson(['accepted' => 0, 'duplicates' => 1]);

        $this->assertSame(1, DB::table('system_logs')->where('source', 'keystone')->count());
    }

    public function test_batch_over_100_events_is_rejected(): void
    {
        $i = $this->makeInstance();
        $events = [];
        for ($n = 0; $n < 101; $n++) {
            $events[] = $this->event(['fingerprint' => "keystone-$n"]);
        }

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => $events])
            ->assertStatus(422);
    }

    public function test_missing_message_is_rejected(): void
    {
        $i = $this->makeInstance();

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [['level' => 'error', 'logged_at' => now()->toIso8601String()]]])
            ->assertStatus(422);
    }

    public function test_error_batch_sends_one_cooled_down_alert(): void
    {
        Notification::fake();
        $i = $this->makeInstance();

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [$this->event(['level' => 'critical', 'fingerprint' => 'keystone-1'])]])
            ->assertOk();

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [$this->event(['level' => 'error', 'fingerprint' => 'keystone-2'])]])
            ->assertOk();

        Notification::assertSentTimes(CriticalLogAlert::class, 1);
    }

    public function test_warning_only_batch_sends_no_alert(): void
    {
        Notification::fake();
        $i = $this->makeInstance();

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [$this->event(['level' => 'warning'])]])
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_oversized_context_is_truncated(): void
    {
        $i = $this->makeInstance();
        $bigTrace = array_fill(0, 500, str_repeat('x', 200)); // ~100 KB

        $this->withToken($i->api_token)
            ->postJson('/ingest/logs', ['events' => [
                $this->event(['context' => ['trace' => $bigTrace, 'file' => 'app/Foo.php']]),
            ]])
            ->assertOk();

        $stored = DB::table('system_logs')->where('source', 'keystone')->value('context');
        $this->assertLessThan(16001, strlen($stored));
        $this->assertJson($stored);
    }
}
