<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPhotoModelTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
    }

    private function product(Tenant $tenant): Product
    {
        return Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true]);
    }

    public function test_promoting_a_photo_to_cover_demotes_its_siblings(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $a = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'is_primary' => true]);
        $b = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'is_primary' => false]);

        $b->update(['is_primary' => true]);

        $this->assertFalse($a->fresh()->is_primary);
        $this->assertTrue($b->fresh()->is_primary);
    }

    public function test_deleting_the_cover_promotes_the_next_by_sort_order(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $cover = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'sort_order' => 0, 'is_primary' => true]);
        $next  = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'sort_order' => 1, 'is_primary' => false]);
        $third = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'c.jpg', 'disk' => 'public', 'sort_order' => 2, 'is_primary' => false]);

        $cover->delete();

        $this->assertTrue($next->fresh()->is_primary);
        $this->assertFalse($third->fresh()->is_primary);
    }

    public function test_deleting_a_non_cover_photo_does_not_disturb_the_cover(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $cover = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'is_primary' => true]);
        $other = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'is_primary' => false]);

        $other->delete();

        $this->assertTrue($cover->fresh()->is_primary);
    }

    public function test_hiding_the_cover_hands_off_to_the_next_visible_photo(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $cover = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'sort_order' => 0, 'is_primary' => true]);
        $next  = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'sort_order' => 1, 'is_primary' => false]);

        $cover->hide('inappropriate');

        $this->assertTrue($cover->fresh()->isHidden());
        $this->assertFalse($cover->fresh()->is_primary);
        $this->assertTrue($next->fresh()->is_primary);
    }

    public function test_hiding_a_non_cover_photo_does_not_touch_the_cover(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $cover = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'is_primary' => true]);
        $other = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'is_primary' => false]);

        $other->hide();

        $this->assertTrue($cover->fresh()->is_primary);
        $this->assertTrue($other->fresh()->isHidden());
    }

    public function test_unhide_clears_the_hidden_fields(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $photo = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'hidden_at' => now(), 'hidden_reason' => 'x']);

        $photo->unhide();

        $this->assertFalse($photo->fresh()->isHidden());
        $this->assertNull($photo->fresh()->hidden_reason);
    }

    public function test_visible_scope_excludes_hidden_photos(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public']);
        $hidden = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public']);
        $hidden->hide(); // hidden_at is deliberately not mass-assignable — see ProductPhoto's docblock

        $this->assertSame(1, ProductPhoto::visible()->count());
    }

    public function test_url_accessors_fall_back_to_the_original_when_derivatives_are_missing(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $photo = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public']);

        $this->assertStringContainsString('storage/a.jpg', $photo->displayUrl());
        $this->assertStringContainsString('storage/a.jpg', $photo->thumbUrl());
    }

    public function test_url_accessors_prefer_derivatives_when_present(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $photo = $product->photos()->create([
            'tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public',
            'path_web' => 'a_w.webp', 'path_thumb' => 'a_t.webp',
        ]);

        $this->assertStringContainsString('storage/a_w.webp', $photo->displayUrl());
        $this->assertStringContainsString('storage/a_t.webp', $photo->thumbUrl());
    }

    public function test_storage_base_path_is_scoped_to_the_product(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $photo = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public']);

        $this->assertSame("products/{$product->id}", $photo->storageBasePath());
    }

    public function test_storage_paths_returns_only_the_set_files(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant);
        $photo = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public']);

        $this->assertSame(['a.jpg'], $photo->storagePaths());

        $photo->update(['path_web' => 'a_w.webp', 'path_thumb' => 'a_t.webp']);
        $this->assertSame(['a.jpg', 'a_w.webp', 'a_t.webp'], $photo->fresh()->storagePaths());
    }
}
