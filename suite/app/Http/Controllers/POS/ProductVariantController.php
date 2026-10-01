<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Product $product)
    {
        $product->load(['variants' => fn ($q) => $q->orderBy('id'), 'photosOrdered']);

        return view('products.variants', compact('product'));
    }

    /**
     * Saves the option axes (e.g. Size: S,M,L / Color: Red,Blue) and
     * generates the resulting variant grid — additively. An existing
     * variant's stock/price/SKU is never touched here, and a combination
     * that's already there is never duplicated; only genuinely new
     * combinations get a row. Removing a value from an axis does NOT
     * delete its variants (which could silently wipe stock history tied
     * to real orders) — it's left for the admin to deactivate manually
     * from the table below, same as any other variant.
     */
    public function generate(Request $request, Product $product)
    {
        $data = $request->validate([
            'options'                => 'required|array|min:1',
            'options.*.name'         => 'required|string|max:50',
            'options.*.values'       => 'required|string|max:500', // comma-separated
        ]);

        $options = [];
        foreach ($data['options'] as $row) {
            $values = collect(explode(',', $row['values']))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($values) {
                $options[trim($row['name'])] = $values;
            }
        }

        if (empty($options)) {
            return back()->with('error', 'Add at least one option with at least one value.');
        }

        $optionCount = array_product(array_map('count', array_values($options)));
        if ($optionCount > 200) {
            return back()->with('error', "That would generate {$optionCount} variants — keep it under 200 (fewer/smaller option lists).");
        }

        $product->update(['variant_options' => $options, 'has_variants' => true]);

        // Cartesian product of every option's values, e.g.
        // Size:[S,M] × Color:[Red,Blue] -> 4 combinations.
        $combinations = [[]];
        foreach ($options as $name => $values) {
            $next = [];
            foreach ($combinations as $combo) {
                foreach ($values as $value) {
                    $next[] = $combo + [$name => $value];
                }
            }
            $combinations = $next;
        }

        $existing = $product->variants()->get()->map(fn ($v) => $this->normalizeAttributes($v->attributes))->all();

        $created = 0;
        foreach ($combinations as $combo) {
            $normalized = $this->normalizeAttributes($combo);
            if (in_array($normalized, $existing, true)) {
                continue; // already have this exact combination
            }

            ProductVariant::create([
                'tenant_id'  => $product->tenant_id,
                'product_id' => $product->id,
                'attributes' => $combo,
                'stock_quantity' => 0,
                'track_stock'    => $product->track_stock,
                'is_active'      => true,
            ]);
            $created++;
        }

        return redirect()->route('products.variants.index', $product)
            ->with('success', $created > 0 ? "{$created} variant(s) added." : 'Options saved — no new combinations to add.');
    }

    /** Bulk-saves the editable fields on each existing variant row. */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'variants'                       => 'required|array',
            'variants.*.id'                  => 'required|integer',
            'variants.*.sku'                 => 'nullable|string|max:100',
            'variants.*.price_override'      => 'nullable|numeric|min:0',
            'variants.*.stock_quantity'      => 'required|integer|min:0',
            'variants.*.track_stock'         => 'nullable|boolean',
            'variants.*.is_active'           => 'nullable|boolean',
            // Validated against this product's own photos only, in the
            // loop below (whereBelongsTo) — not a blanket exists:product_photos,id
            // which would let one tenant's variant point at another
            // tenant's/product's photo row.
            'variants.*.product_photo_id'    => 'nullable|integer',
        ]);

        $photoIds = $product->photos()->pluck('id');

        foreach ($data['variants'] as $row) {
            $variant = $product->variants()->whereKey($row['id'])->first();
            if (! $variant) {
                continue; // belongs to a different product — ignore rather than 404 the whole batch
            }

            $photoId = $row['product_photo_id'] ?? null;
            if ($photoId && ! $photoIds->contains($photoId)) {
                $photoId = null; // posted an id that isn't actually one of this product's photos
            }

            $variant->update([
                'sku'              => $row['sku'] ?? null,
                'price_override'   => $row['price_override'] ?? null,
                'stock_quantity'   => $row['stock_quantity'],
                'track_stock'      => (bool) ($row['track_stock'] ?? false),
                'is_active'        => (bool) ($row['is_active'] ?? false),
                'product_photo_id' => $photoId,
            ]);
        }

        return redirect()->route('products.variants.index', $product)->with('success', 'Variants updated.');
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);

        $variant->delete();

        return redirect()->route('products.variants.index', $product)->with('success', 'Variant removed.');
    }

    /** Sorted by key so the same combination always compares equal regardless of insertion order. */
    private function normalizeAttributes(array $attributes): array
    {
        ksort($attributes);
        return $attributes;
    }
}
