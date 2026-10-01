<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePhotoDerivatives;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Mirrors App\Http\Controllers\Booking\ServicePhotoController's
 * transaction + row-lock pattern exactly (see that class for the full
 * reasoning) — deliberately only reachable from the product EDIT form,
 * not bundled into product creation too. Service's create flow duplicates
 * this same upload logic into ServiceController::storePhotos() as a
 * second, separately-maintained copy; this stays a single code path from
 * day one by requiring a product to exist (and have an id) before a
 * photo can be attached to it.
 */
class ProductPhotoController extends Controller
{
    /** iPhone HEIC is the common rejection — say so in plain language. */
    private const UPLOAD_MESSAGES = [
        'photos.*.image' => 'Each file must be an image.',
        'photos.*.mimes' => 'Photos must be JPG, PNG or WebP. iPhone HEIC photos are not supported yet. In your Camera settings choose "Most Compatible", or share the photo first to convert it to JPG.',
        'photos.*.max'   => 'Each photo must be 4MB or smaller.',
        'photos.max'     => 'A product can have at most :max photos.',
    ];

    /** Attach one or more photos to a product, capped at Product::MAX_PHOTOS. */
    public function store(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $request->validate([
            'photos'   => ['required', 'array', 'min:1', 'max:' . Product::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], self::UPLOAD_MESSAGES);

        $incoming    = array_values($request->file('photos'));
        $dispatchIds = [];

        // Lock the product's photo rows inside the transaction so two
        // concurrent uploads (or an upload racing a cover change) can't
        // exceed the cap or both claim the cover.
        $result = DB::transaction(function () use ($product, $incoming, &$dispatchIds) {
            $existing  = $product->photos()->lockForUpdate()->get(['id', 'sort_order', 'is_primary']);
            $remaining = Product::MAX_PHOTOS - $existing->count();

            if ($remaining <= 0) {
                return ['added' => 0, 'skipped' => count($incoming)];
            }

            $files      = array_slice($incoming, 0, $remaining);
            $order      = (int) $existing->max('sort_order');
            $needsCover = ! $existing->contains('is_primary', true);

            foreach ($files as $i => $file) {
                $photo = $product->photos()->create([
                    'tenant_id'  => $product->tenant_id,
                    'path'       => $file->store("products/{$product->id}", 'public'),
                    'disk'       => 'public',
                    'sort_order' => ++$order,
                    'is_primary' => $needsCover && $i === 0,
                ]);
                $dispatchIds[] = $photo->id;
            }

            return ['added' => count($files), 'skipped' => count($incoming) - count($files)];
        });

        // Queue derivative jobs only after the rows are committed.
        foreach ($dispatchIds as $id) {
            GeneratePhotoDerivatives::dispatch(ProductPhoto::class, $id);
        }

        if ($result['added'] === 0) {
            return back()->withErrors([
                'photos' => 'This product already has the maximum of ' . Product::MAX_PHOTOS . ' photos.',
            ]);
        }

        $message = 'Photos added.' . ($result['skipped'] > 0
            ? " {$result['skipped']} not uploaded — limit is " . Product::MAX_PHOTOS . '.'
            : '');

        return back()->with('success', $message);
    }

    /** Promote a photo to cover. Demote-then-promote under a row lock — never a window with two covers. */
    public function setPrimary(Product $product, ProductPhoto $photo)
    {
        $this->authorizeProduct($product);
        abort_unless((int) $photo->product_id === (int) $product->id, 404);

        if ($photo->isHidden()) {
            return back()->withErrors(['photo' => 'A hidden photo cannot be the cover. Un-hide it first.']);
        }

        DB::transaction(function () use ($product, $photo) {
            $product->photos()->lockForUpdate()->get(['id']);
            $product->photos()->where('id', '!=', $photo->id)->where('is_primary', true)
                ->update(['is_primary' => false]);
            $photo->update(['is_primary' => true]);
        });

        return back()->with('success', 'Cover photo updated.');
    }

    /** Delete a photo and its derivatives; the model promotes the next cover if needed. */
    public function destroy(Product $product, ProductPhoto $photo)
    {
        $this->authorizeProduct($product);
        abort_unless((int) $photo->product_id === (int) $product->id, 404);

        $paths = $photo->storagePaths();

        DB::transaction(function () use ($product, $photo) {
            $product->photos()->lockForUpdate()->get(['id']);
            $photo->delete(); // model hook promotes the next visible cover, in-transaction
        });

        // Remove the files only once the row delete is committed.
        Storage::disk($photo->diskName())->delete($paths);

        return back()->with('success', 'Photo removed.');
    }

    private function authorizeProduct(Product $product): void
    {
        abort_unless((int) $product->tenant_id === (int) auth()->user()->tenant_id, 404);
    }
}
