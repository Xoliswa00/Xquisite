<?php

namespace Tests\Feature\Pos;

use App\Jobs\GeneratePhotoDerivatives;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductPhoto;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductPhotoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
        Storage::fake('public');
    }

    private function manager(Tenant $tenant): User
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('manager');

        return $user;
    }

    private function tenant(): Tenant
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');

        return $tenant;
    }

    private function product(Tenant $tenant, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true,
        ], $overrides));
    }

    public function test_uploading_a_photo_stores_it_and_dispatches_derivative_generation(): void
    {
        Queue::fake();
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);

        $response = $this->actingAs($manager)->post(route('products.photos.store', $product), [
            'photos' => [UploadedFile::fake()->image('mug.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $this->assertSame(1, $product->photos()->count());

        $photo = $product->photos()->first();
        Storage::disk('public')->assertExists($photo->path);
        $this->assertTrue($photo->is_primary, 'The first photo uploaded must become the cover.');

        Queue::assertPushed(GeneratePhotoDerivatives::class, fn ($job) =>
            $job->modelClass === ProductPhoto::class && $job->photoId === $photo->id
        );
    }

    public function test_the_first_upload_becomes_cover_the_second_does_not(): void
    {
        Queue::fake();
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);

        $this->actingAs($manager)->post(route('products.photos.store', $product), [
            'photos' => [UploadedFile::fake()->image('a.jpg')],
        ]);
        $this->actingAs($manager)->post(route('products.photos.store', $product), [
            'photos' => [UploadedFile::fake()->image('b.jpg')],
        ]);

        $photos = $product->photos()->orderBy('id')->get();
        $this->assertTrue($photos[0]->is_primary);
        $this->assertFalse($photos[1]->is_primary);
    }

    /**
     * The 'max:'.Product::MAX_PHOTOS validation rule caps a single
     * request's own batch size (submitting more files than the cap in
     * one go is rejected outright, tested separately below) — the
     * controller's own "slice to remaining capacity" logic exists for
     * the TOTAL (existing + new) exceeding the cap across requests, e.g.
     * 3 already uploaded, 4 more requested (4 is within the per-request
     * max:5, but only 2 slots remain).
     */
    public function test_uploads_beyond_remaining_capacity_are_capped_and_reported(): void
    {
        Queue::fake();
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);

        for ($i = 0; $i < Product::MAX_PHOTOS - 2; $i++) {
            $product->photos()->create([
                'tenant_id' => $tenant->id, 'path' => "products/{$product->id}/seed{$i}.jpg",
                'disk' => 'public', 'sort_order' => $i, 'is_primary' => $i === 0,
            ]);
        }
        $this->assertSame(Product::MAX_PHOTOS - 2, $product->photos()->count());

        // 4 new files, but only 2 slots remain.
        $files = collect(range(1, 4))->map(fn ($i) => UploadedFile::fake()->image("photo{$i}.jpg"))->all();

        $response = $this->actingAs($manager)->post(route('products.photos.store', $product), ['photos' => $files]);

        $response->assertSessionHas('success');
        $this->assertSame(Product::MAX_PHOTOS, $product->photos()->count());
    }

    public function test_a_single_request_submitting_more_than_the_cap_is_rejected_outright(): void
    {
        Queue::fake();
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);

        $files = collect(range(1, Product::MAX_PHOTOS + 2))
            ->map(fn ($i) => UploadedFile::fake()->image("photo{$i}.jpg"))
            ->all();

        $response = $this->actingAs($manager)->post(route('products.photos.store', $product), ['photos' => $files]);

        $response->assertSessionHasErrors('photos');
        $this->assertSame(0, $product->photos()->count());
    }

    public function test_uploading_when_already_at_the_cap_is_rejected(): void
    {
        Queue::fake();
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);

        for ($i = 0; $i < Product::MAX_PHOTOS; $i++) {
            $product->photos()->create([
                'tenant_id' => $tenant->id, 'path' => "products/{$product->id}/seed{$i}.jpg",
                'disk' => 'public', 'sort_order' => $i, 'is_primary' => $i === 0,
            ]);
        }

        $response = $this->actingAs($manager)->post(route('products.photos.store', $product), [
            'photos' => [UploadedFile::fake()->image('overflow.jpg')],
        ]);

        $response->assertSessionHasErrors('photos');
        $this->assertSame(Product::MAX_PHOTOS, $product->photos()->count());
    }

    public function test_setting_a_new_cover_demotes_the_old_one(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);
        $first   = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'is_primary' => true]);
        $second  = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'is_primary' => false]);

        $this->actingAs($manager)->patch(route('products.photos.primary', [$product, $second]))
            ->assertRedirect();

        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
    }

    public function test_a_hidden_photo_cannot_be_made_cover(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);
        $photo   = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public']);
        $photo->hide(); // hidden_at is deliberately not mass-assignable — see ProductPhoto's docblock

        $response = $this->actingAs($manager)->patch(route('products.photos.primary', [$product, $photo]));

        $response->assertSessionHasErrors('photo');
    }

    public function test_deleting_the_cover_promotes_the_next_visible_photo(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);
        $cover   = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'a.jpg', 'disk' => 'public', 'sort_order' => 0, 'is_primary' => true]);
        $next    = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'b.jpg', 'disk' => 'public', 'sort_order' => 1, 'is_primary' => false]);

        $this->actingAs($manager)->delete(route('products.photos.destroy', [$product, $cover]))
            ->assertRedirect();

        $this->assertSame(1, $product->photos()->count());
        $this->assertTrue($next->fresh()->is_primary);
    }

    public function test_deleting_a_photo_removes_its_stored_files(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);

        Storage::disk('public')->put('products/1/orig.jpg', 'x');
        Storage::disk('public')->put('products/1/orig_w.webp', 'x');
        Storage::disk('public')->put('products/1/orig_t.webp', 'x');

        $photo = $product->photos()->create([
            'tenant_id' => $tenant->id, 'path' => 'products/1/orig.jpg', 'disk' => 'public',
            'path_web' => 'products/1/orig_w.webp', 'path_thumb' => 'products/1/orig_t.webp',
        ]);

        $this->actingAs($manager)->delete(route('products.photos.destroy', [$product, $photo]));

        Storage::disk('public')->assertMissing('products/1/orig.jpg');
        Storage::disk('public')->assertMissing('products/1/orig_w.webp');
        Storage::disk('public')->assertMissing('products/1/orig_t.webp');
    }

    public function test_a_manager_cannot_manage_photos_for_another_tenants_product(): void
    {
        $tenantA  = $this->tenant();
        $tenantB  = Tenant::create(['name' => 'Other', 'slug' => 'other-store', 'is_active' => true]);
        $tenantB->activateModule('pos');
        $manager  = $this->manager($tenantA);
        $product  = $this->product($tenantB);

        $this->actingAs($manager)->post(route('products.photos.store', $product), [
            'photos' => [UploadedFile::fake()->image('x.jpg')],
        ])->assertNotFound();
    }

    public function test_heic_upload_is_rejected_with_a_plain_language_message(): void
    {
        Queue::fake();
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $product = $this->product($tenant);

        $response = $this->actingAs($manager)->post(route('products.photos.store', $product), [
            'photos' => [UploadedFile::fake()->create('photo.heic', 100, 'image/heic')],
        ]);

        $response->assertSessionHasErrors('photos.0');
    }
}
