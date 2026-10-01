<?php

namespace Tests\Feature\Pos;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssertsNoNestedForms;
use Tests\TestCase;

/**
 * Catches a real, previously-undetected production bug: a nested <form>
 * (the per-variant "Remove" button's own form, inside the bulk-update
 * form) silently closed the outer form early per the HTML5 parsing spec
 * — the browser drops the inner <form> START tag but still honours its
 * END tag against the still-open outer form. Every PHPUnit test hitting
 * products.variants.update directly (see ProductVariantAdminTest) passed
 * throughout, because none of them render and parse the actual HTML a
 * browser would — they post straight to the route. Found only via real
 * Playwright verification of an unrelated feature (product photos),
 * confirmed pre-existing on dev before that feature touched this file.
 *
 * CORRECTION (found while fixing the same bug class in
 * admin/logs/index.blade.php, a separate follow-up): the "no nested
 * form" check below, via DOMDocument, does NOT actually detect this —
 * confirmed empirically that libxml2's HTML parser does not replicate
 * the real-browser tree-construction quirk this bug depends on, so a
 * DOMDocument-based "no form nested inside a form" assertion PASSES
 * even against genuinely broken markup. The real regression guard is
 * test_the_variants_view_source_has_no_nested_form_elements below (a
 * raw-source scan, see AssertsNoNestedForms) — kept the DOMDocument
 * tests for what they're actually good at (did the right fields end up
 * in the right form), not as proof of "not nested".
 */
class ProductVariantsPageHtmlTest extends TestCase
{
    use RefreshDatabase, AssertsNoNestedForms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    public function test_the_variants_view_source_has_no_nested_form_elements(): void
    {
        $this->assertNoNestedFormsInSource('products/variants.blade.php');
    }

    public function test_the_save_variants_button_is_inside_the_update_form_not_orphaned(): void
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true, 'has_variants' => true]);
        ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Size' => 'M'], 'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
        ]);

        $html = $this->actingAs($manager)->get(route('products.variants.index', $product))->getContent();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $saveForm = $dom->getElementById('variants-save-form');
        $this->assertNotNull($saveForm, 'The update form must have id="variants-save-form".');

        // "Save Variants" must be a descendant of that exact form element.
        $saveButtons = $xpath->query('.//button[contains(., "Save Variants")]', $saveForm);
        $this->assertGreaterThan(0, $saveButtons->length, 'The "Save Variants" button must render inside #variants-save-form, not after it was silently closed early.');
    }

    public function test_each_variant_has_a_standalone_destroy_form_targeted_by_the_remove_button(): void
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true, 'has_variants' => true]);
        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Size' => 'M'], 'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
        ]);

        $html = $this->actingAs($manager)->get(route('products.variants.index', $product))->getContent();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $destroyForm = $dom->getElementById("destroy-variant-{$variant->id}");
        $this->assertNotNull($destroyForm, 'Each variant needs its own standalone destroy form.');
        $this->assertSame('POST', $destroyForm->getAttribute('method'));

        $xpath = new \DOMXPath($dom);
        $removeButtons = $xpath->query("//button[@form='destroy-variant-{$variant->id}']");
        $this->assertSame(1, $removeButtons->length, 'The Remove button must reference the destroy form by id via the form="" attribute.');
    }
}
