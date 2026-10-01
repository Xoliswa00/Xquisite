<?php

namespace Tests\Feature;

use App\Jobs\GeneratePhotoDerivatives;
use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Tests\TestCase;

/**
 * Runs the job for real (no Queue::fake()) against a genuine generated
 * image — this is the one piece of the service-photos pattern that had
 * zero test coverage anywhere in the app before this feature; worth
 * actually exercising the real GD/Intervention path once, not just
 * asserting the job gets dispatched.
 */
class GeneratePhotoDerivativesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function photo(): ProductPhoto
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true]);

        // A genuine 1600x1200 JPEG, not a 1x1 placeholder — scaleDown()/cover()
        // need real dimensions to prove the resize/crop math actually ran.
        $image = ImageManager::gd()->create(1600, 1200)->fill('ff0000');
        $path  = "products/{$product->id}/original.jpg";
        Storage::disk('public')->put($path, (string) $image->toJpeg());

        return $product->photos()->create([
            'tenant_id' => $tenant->id, 'path' => $path, 'disk' => 'public', 'sort_order' => 0, 'is_primary' => true,
        ]);
    }

    public function test_it_generates_web_and_thumb_derivatives_with_correct_dimensions(): void
    {
        $photo = $this->photo();

        (new GeneratePhotoDerivatives(ProductPhoto::class, $photo->id))->handle();

        $photo->refresh();
        $this->assertNotNull($photo->path_web);
        $this->assertNotNull($photo->path_thumb);
        Storage::disk('public')->assertExists($photo->path_web);
        Storage::disk('public')->assertExists($photo->path_thumb);

        // scaleDown(width: 1400) on a 1600-wide source — scaled down, aspect kept.
        $this->assertSame(1400, $photo->width);
        $this->assertSame(1050, $photo->height); // 1600:1200 is 4:3, so 1400 wide -> 1050 tall

        $thumb = ImageManager::gd()->read(Storage::disk('public')->get($photo->path_thumb));
        $this->assertSame(600, $thumb->width());
        $this->assertSame(400, $thumb->height());
    }

    public function test_derivative_paths_live_under_the_photos_storage_base_path(): void
    {
        $photo = $this->photo();

        (new GeneratePhotoDerivatives(ProductPhoto::class, $photo->id))->handle();

        $photo->refresh();
        $base = $photo->storageBasePath();
        $this->assertStringStartsWith($base . '/', $photo->path_web);
        $this->assertStringStartsWith($base . '/', $photo->path_thumb);
    }

    public function test_a_missing_source_file_is_skipped_without_throwing(): void
    {
        $photo = $this->photo();
        Storage::disk('public')->delete($photo->path);

        (new GeneratePhotoDerivatives(ProductPhoto::class, $photo->id))->handle();

        $this->assertNull($photo->fresh()->path_web);
    }

    public function test_a_deleted_photo_is_a_silent_no_op(): void
    {
        $photo = $this->photo();
        $id    = $photo->id;
        $photo->delete();

        // Must not throw even though the row is gone.
        (new GeneratePhotoDerivatives(ProductPhoto::class, $id))->handle();

        $this->assertNull(ProductPhoto::find($id));
    }

    public function test_saving_derivatives_does_not_disturb_the_cover_flag(): void
    {
        $photo = $this->photo();
        $this->assertTrue($photo->is_primary);

        (new GeneratePhotoDerivatives(ProductPhoto::class, $photo->id))->handle();

        // forceFill()->saveQuietly() must not trip the booted() cover-
        // invariant hook (which only acts when is_primary itself changes).
        $this->assertTrue($photo->fresh()->is_primary);
    }
}
