<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssertsNoNestedForms;
use Tests\TestCase;

/**
 * Catches a real, previously-undetected bug: the "Resolve all" <form>
 * was nested inside the filter <form> above it. Per the HTML5 parsing
 * spec, a browser drops the inner <form> START tag but still honours
 * its END tag against the still-open outer form — silently closing the
 * filter form early, right after the "Resolve all" button. Same
 * underlying bug class and fix pattern as products/variants.blade.php
 * (PR #156) — found while proactively checking for the same bug class
 * elsewhere after fixing that one.
 *
 * The actual nesting check is a raw-source scan (see
 * AssertsNoNestedForms), NOT a DOMDocument/libxml2 structural check —
 * confirmed empirically that libxml2 does not replicate the browser
 * quirk this bug depends on, so a DOMDocument-based "no form nested
 * inside a form" assertion passes even against the ORIGINAL broken
 * markup. The DOM-based tests below are kept only for what they're
 * actually good at (did the right fields end up in the right form),
 * not as the regression guard for the nesting bug itself.
 */
class AdminLogsPageHtmlTest extends TestCase
{
    use RefreshDatabase, AssertsNoNestedForms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }

    public function test_the_logs_view_source_has_no_nested_form_elements(): void
    {
        $this->assertNoNestedFormsInSource('admin/logs/index.blade.php');
    }

    public function test_the_resolve_all_button_is_inside_its_own_form_not_the_filter_form(): void
    {
        $user = $this->superAdmin();

        $html = $this->actingAs($user)->get(route('admin.logs.index'))->getContent();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $resolveAllButtons = $xpath->query('//button[contains(., "Resolve all")]');
        $this->assertSame(1, $resolveAllButtons->length);

        $button = $resolveAllButtons->item(0);
        $enclosingForm = null;
        for ($node = $button->parentNode; $node; $node = $node->parentNode) {
            if ($node instanceof \DOMElement && strtolower($node->tagName) === 'form') {
                $enclosingForm = $node;
                break;
            }
        }

        $this->assertNotNull($enclosingForm, 'The "Resolve all" button must be inside a <form>.');
        $this->assertSame('POST', strtoupper($enclosingForm->getAttribute('method')));
        $this->assertStringContainsString('resolve-all', $enclosingForm->getAttribute('action'));
    }

    public function test_the_filter_form_still_wraps_the_search_and_select_fields(): void
    {
        $user = $this->superAdmin();

        $html = $this->actingAs($user)->get(route('admin.logs.index'))->getContent();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $filterForm = $dom->getElementById('filter-form');
        $this->assertNotNull($filterForm);
        $this->assertSame('GET', strtoupper($filterForm->getAttribute('method')));

        $selects = $filterForm->getElementsByTagName('select');
        $this->assertSame(3, $selects->length, 'The filter form must still contain all 3 filter selects (level/status/source).');
    }
}
