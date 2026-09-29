<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackPageView;
use App\Models\FoundingTwentyApplication;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * First-party, cookieless visitor stats: who visits, which pages, what they click,
 * without storing an IP address or anything that identifies a person.
 */
class TrafficAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const DESKTOP = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36';
    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1';

    protected function setUp(): void
    {
        parent::setUp();
        config(['analytics.enabled' => true]);
    }

    private function visit(string $uri = '/founding-20', string $ua = self::DESKTOP, array $headers = [])
    {
        return $this->withHeaders(['User-Agent' => $ua] + $headers)->get($uri);
    }

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create(['tenant_id' => null]);
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    private function pageView(array $o = []): int
    {
        return DB::table('page_views')->insertGetId(array_merge([
            'visitor_hash' => substr(md5((string) microtime(true) . rand()), 0, 16), 'path' => '/founding-20', 'device' => 'mobile',
            'is_authenticated' => false, 'created_at' => now(),
        ], $o));
    }

    private function click(int $pv, array $o = []): void
    {
        DB::table('click_events')->insert(array_merge([
            'page_view_id' => $pv, 'path' => '/founding-20', 'x_pct' => 50, 'y_px' => 400, 'viewport' => 'mobile', 'kind' => 'link',
            'label' => 'Start Your Application', 'href' => '/founding-20/apply', 'created_at' => now(),
        ], $o));
    }

    // ── What gets counted ────────────────────────────────────────────────────

    public function test_a_visit_to_a_public_page_is_recorded_without_any_identifying_data(): void
    {
        $this->visit('/founding-20')->assertOk();

        $row = DB::table('page_views')->sole();
        $this->assertSame('/founding-20', $row->path);
        $this->assertSame('desktop', $row->device);
        $this->assertFalse((bool) $row->is_authenticated);
        $this->assertSame(16, strlen($row->visitor_hash));
        $this->assertStringNotContainsString('127.0.0.1', json_encode($row));
        $this->assertStringNotContainsString('Chrome', json_encode($row));
        $this->assertEqualsCanonicalizing(
            ['id', 'visitor_hash', 'path', 'source', 'campaign', 'referrer_host', 'device', 'is_authenticated', 'max_scroll', 'duration_seconds', 'created_at'],
            Schema::getColumnListing('page_views')
        );
    }

    public function test_the_page_gets_the_tracking_script_with_a_matching_token_and_no_cookie(): void
    {
        $response = $this->visit('/founding-20');

        $id = DB::table('page_views')->value('id');
        $html = $response->getContent();
        $this->assertStringContainsString('src="' . asset('js/xq-track.js') . '"', $html);
        $this->assertStringContainsString('data-pv="' . $id . '"', $html);
        $this->assertStringContainsString('data-t="' . TrackPageView::token($id) . '"', $html);
        $this->assertLessThan(strripos($html, '</html>'), strripos($html, 'xq-track.js'));
        $this->assertStringContainsString('</body>', $html);
        $this->assertNull(collect($response->headers->getCookies())->first(fn ($c) => str_contains(strtolower($c->getName()), 'track') || str_contains(strtolower($c->getName()), 'visitor')));
    }

    public function test_devices_are_told_apart(): void
    {
        $this->visit('/founding-20', self::IPHONE);
        $this->visit('/founding-20', 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1');
        $this->visit('/founding-20', self::DESKTOP);

        $this->assertSame(['mobile', 'tablet', 'desktop'], DB::table('page_views')->orderBy('id')->pluck('device')->all());
    }

    public function test_where_the_visit_came_from_is_kept_but_our_own_pages_are_not_a_source(): void
    {
        $this->visit('/founding-20?src=WhatsApp&utm_campaign=sept-wave');
        $this->visit('/founding-20', self::DESKTOP, ['Referer' => 'https://www.tiktok.com/@xquisite']);
        $this->visit('/founding-20', self::DESKTOP, ['Referer' => url('/somewhere')]);

        $rows = DB::table('page_views')->orderBy('id')->get();
        $this->assertSame('whatsapp', $rows[0]->source);
        $this->assertSame('sept-wave', $rows[0]->campaign);
        $this->assertSame('www.tiktok.com', $rows[1]->referrer_host);
        $this->assertNull($rows[2]->referrer_host);
    }

    public function test_the_page_address_is_stored_as_a_pattern_never_with_the_ids_or_tokens_in_the_link(): void
    {
        $a = FoundingTwentyApplication::create([
            'owner_name' => 'T', 'business_name' => 'B', 'phone' => '0821234567', 'preferred_contact_method' => 'whatsapp',
            'status' => 'selected', 'deposit_amount' => 100, 'deposit_reference' => 'F20-0001', 'submitted_at' => now(),
        ]);
        $token = $a->reservationToken();

        $this->visit(route('founding-twenty.reserve', [$a, $token], false))->assertOk();

        $path = DB::table('page_views')->value('path');
        $this->assertSame('/founding-20/reserve/{foundingTwenty}/{token}', $path);
        $this->assertStringNotContainsString($token, $path);
    }

    public function test_the_same_person_is_one_visitor_within_a_day_and_a_stranger_the_next_day(): void
    {
        $this->visit('/founding-20');
        $this->visit('/founding-20');
        $this->visit('/founding-20', self::IPHONE);
        $hashes = DB::table('page_views')->orderBy('id')->pluck('visitor_hash');
        $this->assertSame($hashes[0], $hashes[1]);
        $this->assertNotSame($hashes[0], $hashes[2]);

        $this->travel(1)->days();
        $this->visit('/founding-20');
        $this->assertNotSame($hashes[0], DB::table('page_views')->orderByDesc('id')->value('visitor_hash'));
    }

    public function test_the_app_is_counted_too_but_marked_as_inside_the_app_with_no_user_recorded(): void
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-' . uniqid(), 'email' => 't@example.com', 'is_active' => true]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->withHeaders(['User-Agent' => self::DESKTOP])->get('/founding-20')->assertOk();

        $row = DB::table('page_views')->sole();
        $this->assertTrue((bool) $row->is_authenticated);
        $this->assertStringNotContainsString((string) $user->email, json_encode($row));
    }

    // ── What is never counted ────────────────────────────────────────────────

    public function test_bots_link_previews_and_our_own_monitoring_are_ignored(): void
    {
        foreach (['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'WhatsApp/2.23.20 A', 'facebookexternalhit/1.1', 'curl/8.4.0', 'Mozilla/5.0 HeadlessChrome/120', 'UptimeRobot/2.0', ''] as $ua) {
            $this->visit('/founding-20', $ua);
        }

        $this->assertSame(0, DB::table('page_views')->count());
    }

    public function test_do_not_track_and_global_privacy_control_are_honoured(): void
    {
        $this->visit('/founding-20', self::DESKTOP, ['DNT' => '1']);
        $this->visit('/founding-20', self::DESKTOP, ['Sec-GPC' => '1']);

        $this->assertSame(0, DB::table('page_views')->count());
    }

    public function test_prefetching_and_the_heat_map_preview_do_not_count(): void
    {
        $this->visit('/founding-20', self::DESKTOP, ['Sec-Purpose' => 'prefetch']);
        $this->visit('/founding-20?xq_no_track=1');

        $this->assertSame(0, DB::table('page_views')->count());
    }

    public function test_only_successful_html_page_loads_are_counted(): void
    {
        $this->visit('/no-such-page');                                            // 404
        $this->withHeaders(['User-Agent' => self::DESKTOP, 'Accept' => 'application/json'])->get('/founding-20'); // JSON
        $this->withHeaders(['User-Agent' => self::DESKTOP, 'X-Requested-With' => 'XMLHttpRequest'])->get('/founding-20'); // ajax
        $this->withHeaders(['User-Agent' => self::DESKTOP])->post('/founding-20/questions', ['question' => 'Hi?']); // POST

        $this->assertSame(0, DB::table('page_views')->count());
    }

    public function test_the_back_office_and_our_own_team_are_never_counted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->withHeaders(['User-Agent' => self::DESKTOP])->get(route('admin.founding-twenty.index'))->assertOk();
        $this->actingAs($admin)->withHeaders(['User-Agent' => self::DESKTOP])->get('/founding-20')->assertOk();

        $this->assertSame(0, DB::table('page_views')->count());
    }

    public function test_it_can_be_switched_off(): void
    {
        config(['analytics.enabled' => false]);

        $response = $this->visit('/founding-20')->assertOk();

        $this->assertSame(0, DB::table('page_views')->count());
        $this->assertStringNotContainsString('xq-track.js', $response->getContent());
    }

    public function test_a_tracking_failure_never_breaks_the_page(): void
    {
        Schema::drop('click_events');
        Schema::drop('page_views');

        $this->visit('/founding-20')->assertOk()->assertSee('Start Your Application');
    }

    // ── The beacon ───────────────────────────────────────────────────────────

    private function beacon(int $pv, array $fields = [], ?string $token = null)
    {
        return $this->post(route('traffic.beacon'), array_merge(['pv' => $pv, 't' => $token ?? TrackPageView::token($pv)], $fields));
    }

    public function test_scroll_depth_and_time_on_page_are_saved_and_never_go_backwards(): void
    {
        $pv = $this->pageView();

        $this->beacon($pv, ['scroll' => 60, 'seconds' => 45])->assertNoContent();
        $this->beacon($pv, ['scroll' => 30, 'seconds' => 20])->assertNoContent();

        $row = DB::table('page_views')->find($pv);
        $this->assertSame(60, (int) $row->max_scroll);
        $this->assertSame(45, (int) $row->duration_seconds);
    }

    public function test_absurd_values_are_clamped(): void
    {
        $pv = $this->pageView();

        $this->beacon($pv, ['scroll' => 9999, 'seconds' => 999999]);

        $row = DB::table('page_views')->find($pv);
        $this->assertSame(100, (int) $row->max_scroll);
        $this->assertSame(3600, (int) $row->duration_seconds);
    }

    public function test_clicks_are_saved_with_where_and_what_was_pressed(): void
    {
        $pv = $this->pageView(['path' => '/founding-20']);
        $clicks = [
            ['x' => 48.2, 'y' => 812, 'v' => 'mobile', 'k' => 'link', 'l' => 'Start Your Application', 'h' => '/founding-20/apply'],
            ['x' => 12, 'y' => 40, 'v' => 'desktop', 'k' => 'weird', 'l' => '', 'h' => ''],
        ];

        $this->beacon($pv, ['clicks' => json_encode($clicks)])->assertNoContent();

        $rows = DB::table('click_events')->orderBy('id')->get();
        $this->assertCount(2, $rows);
        $this->assertSame('/founding-20', $rows[0]->path);
        $this->assertSame('mobile', $rows[0]->viewport);
        $this->assertSame('link', $rows[0]->kind);
        $this->assertSame('Start Your Application', $rows[0]->label);
        $this->assertSame('/founding-20/apply', $rows[0]->href);
        $this->assertSame(812, (int) $rows[0]->y_px);
        $this->assertSame('other', $rows[1]->kind);
        $this->assertNull($rows[1]->label);
        $this->assertNull($rows[1]->href);
    }

    public function test_click_positions_and_labels_are_clamped_to_sane_sizes(): void
    {
        $pv = $this->pageView();

        $this->beacon($pv, ['clicks' => json_encode([['x' => 500, 'y' => -5, 'v' => 'mobile', 'k' => 'button', 'l' => str_repeat('a', 500), 'h' => '/x']])]);

        $row = DB::table('click_events')->sole();
        $this->assertEquals(100, $row->x_pct);
        $this->assertSame(0, (int) $row->y_px);
        $this->assertSame(80, strlen($row->label));
    }

    public function test_a_page_view_cannot_be_flooded_with_clicks(): void
    {
        $pv = $this->pageView();
        $many = array_fill(0, 100, ['x' => 10, 'y' => 10, 'v' => 'mobile', 'k' => 'link', 'l' => 'x', 'h' => '/x']);

        $this->beacon($pv, ['clicks' => json_encode($many)]);
        $this->assertSame(30, DB::table('click_events')->count());

        for ($i = 0; $i < 12; $i++) {
            $this->beacon($pv, ['clicks' => json_encode($many)]);
        }
        $this->assertSame(300, DB::table('click_events')->count());
    }

    public function test_a_wrong_token_or_an_old_page_view_is_silently_ignored(): void
    {
        $pv = $this->pageView();
        $old = $this->pageView(['created_at' => now()->subDays(3)]);

        $this->beacon($pv, ['scroll' => 80], 'not-the-token')->assertNoContent();
        $this->beacon($old, ['scroll' => 80])->assertNoContent();
        $this->beacon(999999, ['scroll' => 80])->assertNoContent();

        $this->assertNull(DB::table('page_views')->find($pv)->max_scroll);
        $this->assertNull(DB::table('page_views')->find($old)->max_scroll);
    }

    public function test_junk_clicks_data_is_harmless(): void
    {
        $pv = $this->pageView();

        $this->beacon($pv, ['clicks' => '{not json'])->assertNoContent();
        $this->beacon($pv, ['clicks' => json_encode([1, 'a', ['x' => 1]])])->assertNoContent();

        $this->assertSame(0, DB::table('click_events')->count());
    }

    public function test_the_tracking_script_ships_and_reports_through_a_beacon(): void
    {
        $js = file_get_contents(public_path('js/xq-track.js'));

        $this->assertStringContainsString('sendBeacon', $js);
        $this->assertStringNotContainsString('localStorage', $js);
        $this->assertStringNotContainsString('document.cookie', $js);
    }

    // ── The report ───────────────────────────────────────────────────────────

    public function test_only_signed_in_admins_can_see_the_traffic_pages(): void
    {
        $this->get(route('admin.traffic.index'))->assertRedirect();
        $this->get(route('admin.traffic.heatmap'))->assertRedirect();
    }

    public function test_the_report_adds_up_visitors_views_time_and_scroll(): void
    {
        $a = 'aaaaaaaaaaaaaaaa';
        $b = 'bbbbbbbbbbbbbbbb';
        $this->pageView(['visitor_hash' => $a, 'path' => '/', 'max_scroll' => 80, 'duration_seconds' => 40]);
        $this->pageView(['visitor_hash' => $a, 'path' => '/founding-20', 'max_scroll' => 40, 'duration_seconds' => 20]);
        $this->pageView(['visitor_hash' => $b, 'path' => '/founding-20']);
        $this->pageView(['visitor_hash' => $a, 'path' => '/', 'created_at' => now()->subDays(1)]); // same person, another day
        $this->pageView(['visitor_hash' => $b, 'path' => '/', 'created_at' => now()->subDays(20)]); // outside 7 days

        $response = $this->actingAs($this->admin())->get(route('admin.traffic.index', ['days' => 7]));

        $response->assertOk();
        $this->assertSame(4, $response->viewData('totals')['views']);
        $this->assertSame(3, $response->viewData('totals')['visitors']); // a and b today, a yesterday
        $this->assertSame(30, (int) $response->viewData('totals')['avg_seconds']);
        $this->assertSame(60, (int) $response->viewData('totals')['avg_scroll']);
        $this->assertCount(7, $response->viewData('series'));
        $this->assertSame(2, $response->viewData('topPages')->firstWhere('path', '/founding-20')->views);

        $wide = $this->actingAs($this->admin())->get(route('admin.traffic.index', ['days' => 30]));
        $this->assertSame(5, $wide->viewData('totals')['views']);
    }

    public function test_top_pages_are_ordered_by_views(): void
    {
        foreach ([['/', 1], ['/founding-20', 3], ['/about', 2]] as [$path, $n]) {
            for ($i = 0; $i < $n; $i++) {
                $this->pageView(['path' => $path]);
            }
        }

        $paths = $this->actingAs($this->admin())->get(route('admin.traffic.index'))->viewData('topPages')->pluck('path')->all();

        $this->assertSame(['/founding-20', '/about', '/'], $paths);
    }

    public function test_the_public_site_and_the_app_can_be_viewed_separately(): void
    {
        $this->pageView(['path' => '/founding-20']);
        $this->pageView(['path' => '/customers', 'is_authenticated' => true]);
        $this->pageView(['path' => '/customers', 'is_authenticated' => true]);
        $admin = $this->admin();

        $this->assertSame(1, $this->actingAs($admin)->get(route('admin.traffic.index'))->viewData('totals')['views']);
        $this->assertSame(2, $this->actingAs($admin)->get(route('admin.traffic.index', ['scope' => 'app']))->viewData('totals')['views']);
        $this->assertSame(3, $this->actingAs($admin)->get(route('admin.traffic.index', ['scope' => 'all']))->viewData('totals')['views']);
    }

    public function test_sources_fall_back_from_src_to_referrer_to_direct(): void
    {
        $this->pageView(['source' => 'whatsapp']);
        $this->pageView(['source' => 'whatsapp']);
        $this->pageView(['referrer_host' => 'www.tiktok.com']);
        $this->pageView([]);

        $sources = $this->actingAs($this->admin())->get(route('admin.traffic.index'))->viewData('sources')->pluck('views', 'origin')->all();

        $this->assertSame(['whatsapp' => 2, 'www.tiktok.com' => 1, 'Direct' => 1], $sources);
    }

    public function test_most_clicked_buttons_count_real_targets_and_skip_stray_clicks(): void
    {
        $pv = $this->pageView();
        foreach (range(1, 3) as $i) {
            $this->click($pv);
        }
        $this->click($pv, ['label' => 'Ask a question', 'href' => null, 'kind' => 'button']);
        $this->click($pv, ['label' => null, 'href' => null, 'kind' => 'other']);

        $clicks = $this->actingAs($this->admin())->get(route('admin.traffic.index'))->viewData('topClicks');

        $this->assertSame(['Start Your Application' => 3, 'Ask a question' => 1], $clicks->pluck('clicks', 'label')->map(fn ($c) => (int) $c)->all());
    }

    public function test_founding_20_shows_the_journey_the_change_and_the_real_applications(): void
    {
        foreach (['aaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 'cccccccccccccccc', 'dddddddddddddddd'] as $h) {
            $this->pageView(['visitor_hash' => $h, 'path' => '/founding-20']);
        }
        foreach (['aaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb'] as $h) {
            $this->pageView(['visitor_hash' => $h, 'path' => '/founding-20/apply']);
        }
        $this->pageView(['visitor_hash' => 'aaaaaaaaaaaaaaaa', 'path' => '/founding-20/apply/questions']);
        $this->pageView(['visitor_hash' => 'eeeeeeeeeeeeeeee', 'path' => '/founding-20', 'created_at' => now()->subDays(9)]); // previous period
        $pv = $this->pageView(['path' => '/founding-20']);
        $this->click($pv);
        FoundingTwentyApplication::create(['owner_name' => 'T', 'business_name' => 'B', 'phone' => '0821234567', 'preferred_contact_method' => 'whatsapp', 'submitted_at' => now()]);

        $f = $this->actingAs($this->admin())->get(route('admin.traffic.index'))->viewData('founding20');

        $this->assertSame([5, 2, 1, 0], array_column($f['steps'], 'visitors'));
        $this->assertSame([null, 40, 50, 0], array_column($f['steps'], 'of_previous'));
        $this->assertSame(1, $f['previous_visitors']);
        $this->assertSame(400, $f['change']);
        $this->assertSame(1, $f['cta_clicks']);
        $this->assertSame(1, $f['started']);
        $this->assertSame(1, $f['submitted']);
    }

    public function test_the_report_is_empty_safe_and_the_nav_links_to_it(): void
    {
        $this->actingAs($this->admin())->get(route('admin.traffic.index'))
            ->assertOk()
            ->assertSee('No visits recorded in this period yet.')
            ->assertSee('No clicks recorded yet.')
            ->assertSee('Site Traffic')
            ->assertSee('Founding 20');
    }

    // ── The heat map ─────────────────────────────────────────────────────────

    public function test_the_heat_map_shows_the_page_with_the_click_points_for_the_chosen_device(): void
    {
        $pv = $this->pageView();
        $this->click($pv, ['x_pct' => 41.5, 'y_px' => 620, 'viewport' => 'mobile']);
        $this->click($pv, ['x_pct' => 70, 'y_px' => 100, 'viewport' => 'desktop']);

        $response = $this->actingAs($this->admin())->get(route('admin.traffic.heatmap', ['path' => '/founding-20', 'device' => 'mobile']));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('points')->count());
        $this->assertEquals(41.5, $response->viewData('points')->first()->x_pct);
        $response->assertSee('src="' . url('/founding-20') . '?xq_no_track=1"', false);
        $response->assertSee('Start Your Application');

        $desktop = $this->actingAs($this->admin())->get(route('admin.traffic.heatmap', ['path' => '/founding-20', 'device' => 'desktop']));
        $this->assertEquals(70, $desktop->viewData('points')->first()->x_pct);
    }

    public function test_the_heat_map_reports_how_far_down_people_read(): void
    {
        foreach ([10, 30, 60, 100] as $scroll) {
            $this->click($this->pageView(['max_scroll' => $scroll]));
        }

        $reach = $this->actingAs($this->admin())->get(route('admin.traffic.heatmap'))->viewData('reach');

        $this->assertSame([4, 3, 2, 1, 1], [(int) $reach->total, (int) $reach->q1, (int) $reach->q2, (int) $reach->q3, (int) $reach->q4]);
    }

    public function test_pages_with_ids_in_the_address_are_not_offered_for_the_heat_map(): void
    {
        $this->click($this->pageView(), ['path' => '/founding-20/reserve/{foundingTwenty}/{token}']);
        $this->click($this->pageView(), ['path' => '/founding-20']);

        $pages = $this->actingAs($this->admin())->get(route('admin.traffic.heatmap'))->viewData('pages')->pluck('path')->all();

        $this->assertSame(['/founding-20'], $pages);
    }

    public function test_the_heat_map_says_so_when_there_is_nothing_yet(): void
    {
        $this->actingAs($this->admin())->get(route('admin.traffic.heatmap'))->assertOk()->assertSee('No clicks recorded yet.');
    }

    // ── Keeping it small and honest ──────────────────────────────────────────

    public function test_old_stats_are_deleted_after_the_retention_period_and_recent_ones_stay(): void
    {
        $old = $this->pageView(['created_at' => now()->subDays(181)]);
        $this->click($old, ['created_at' => now()->subDays(181)]);
        $recent = $this->pageView(['created_at' => now()->subDays(10)]);
        $this->click($recent);

        $this->artisan('traffic:purge')->assertSuccessful();

        $this->assertSame([$recent], DB::table('page_views')->pluck('id')->all());
        $this->assertSame(1, DB::table('click_events')->count());
    }

    public function test_the_privacy_policy_explains_the_counting_and_the_retention(): void
    {
        $this->get(route('privacy'))
            ->assertSee('We count visits to understand which pages are useful')
            ->assertSee('without')
            ->assertSee('deleted after 180 days')
            ->assertSee('Do Not Track');
    }
}
