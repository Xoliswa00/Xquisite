<?php

namespace Tests\Feature\Pos;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
 * A DOMDocument parse of the real rendered response is the cheap,
 * deterministic equivalent of "did a browser's form-association logic
 * break" — it would have caught this on day one.
 */
class ProductVariantsPageHtmlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    public function test_the_variants_page_has_no_nested_form_elements(): void
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
        @$dom->loadHTML($html); // suppresses warnings for the page's non-HTML5 bits DOMDocument doesn't know

        foreach ($dom->getElementsByTagName('form') as $form) {
            $nested = $form->getElementsByTagName('form');
            $this->assertSame(0, $nested->length, 'Found a <form> nested inside another <form> — invalid HTML; the browser silently closes the outer form early at the inner form\'s closing tag.');
        }
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
