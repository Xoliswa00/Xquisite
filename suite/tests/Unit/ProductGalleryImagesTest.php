<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductGalleryImagesTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
    }

    public function test_gallery_images_falls_back_to_the_legacy_url_when_no_photos_uploaded(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true,
            'image_url' => 'https://example.com/legacy.jpg',
        ]);

        $this->assertSame(['https://example.com/legacy.jpg'], $product->gallery_images);
    }

    public function test_gallery_images_is_empty_with_no_photos_and_no_legacy_url(): void
    {
        $tenant = $this->tenant();
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true]);

        $this->assertSame([], $product->gallery_images);
    }

    public function test_gallery_images_returns_the_real_uploaded_set_cover_first_ignoring_the_legacy_url(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true,
            'image_url' => 'https://example.com/legacy.jpg',
        ]);
        $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'sort_order' => 1, 'is_primary' => false]);
        $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'sort_order' => 0, 'is_primary' => true]);

        $images = $product->fresh()->gallery_images;

        $this->assertCount(2, $images);
        $this->assertStringContainsString('a.jpg', $images[0], 'Cover photo must be first.');
        $this->assertStringNotContainsString('legacy.jpg', implode('', $images));
    }

    public function test_gallery_images_excludes_hidden_photos(): void
    {
        $tenant = $this->tenant();
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true]);
        $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public']);
        $hidden = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public']);
        $hidden->hide(); // hidden_at is deliberately not mass-assignable — see ProductPhoto's docblock

        $this->assertCount(1, $product->fresh()->gallery_images);
    }

    public function test_image_url_accessor_resolves_to_the_cover_photo_when_one_exists(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true,
            'image_url' => 'https://example.com/legacy.jpg',
        ]);
        $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'cover.jpg', 'disk' => 'public', 'is_primary' => true]);

        $this->assertStringContainsString('cover.jpg', $product->fresh()->image_url);
        $this->assertStringNotContainsString('legacy.jpg', $product->fresh()->image_url);
    }

    public function test_image_url_accessor_falls_back_to_the_legacy_value_with_no_cover_photo(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true,
            'image_url' => 'https://example.com/legacy.jpg',
        ]);

        $this->assertSame('https://example.com/legacy.jpg', $product->image_url);
    }

    public function test_raw_original_bypasses_the_cover_photo_override(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true,
            'image_url' => 'https://example.com/legacy.jpg',
        ]);
        $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'cover.jpg', 'disk' => 'public', 'is_primary' => true]);

        // The exact guard that prevents re-saving the edit form from
        // silently overwriting the legacy column with the resolved URL.
        $this->assertSame('https://example.com/legacy.jpg', $product->fresh()->getRawOriginal('image_url'));
    }

    public function test_variant_effective_image_url_prefers_a_linked_product_photo(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true,
            'has_variants' => true, 'image_url' => 'https://example.com/parent.jpg',
        ]);
        $photo = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'red.jpg', 'disk' => 'public']);
        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Color' => 'Red'], 'is_active' => true,
            'image_url' => 'https://example.com/manual-red.jpg',
            'product_photo_id' => $photo->id,
        ]);

        $this->assertStringContainsString('red.jpg', $variant->effectiveImageUrl());
    }

    public function test_variant_effective_image_url_falls_back_to_its_own_pasted_url_without_a_linked_photo(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true,
            'has_variants' => true, 'image_url' => 'https://example.com/parent.jpg',
        ]);
        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Color' => 'Red'], 'is_active' => true,
            'image_url' => 'https://example.com/manual-red.jpg',
        ]);

        $this->assertSame('https://example.com/manual-red.jpg', $variant->effectiveImageUrl());
    }

    public function test_variant_effective_image_url_falls_back_to_the_parent_product_with_nothing_of_its_own(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true,
            'has_variants' => true, 'image_url' => 'https://example.com/parent.jpg',
        ]);
        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Color' => 'Red'], 'is_active' => true,
        ]);

        $this->assertSame('https://example.com/parent.jpg', $variant->effectiveImageUrl());
    }

    public function test_deleting_a_linked_photo_clears_the_variants_reference_instead_of_leaving_it_dangling(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true, 'has_variants' => true,
        ]);
        $photo = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'red.jpg', 'disk' => 'public']);
        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Color' => 'Red'], 'is_active' => true, 'product_photo_id' => $photo->id,
        ]);

        $photo->delete();

        $this->assertNull($variant->fresh()->product_photo_id);
    }
}
