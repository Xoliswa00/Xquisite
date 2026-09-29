{{--
    Small stock-urgency badge shown on a product image (index cards, related
    products, product detail page). One shared partial so the three
    thresholds (out of stock / low stock / in stock) can't drift between the
    places that render a product card — see feedback_alert_color_palette.md:
    red = error/blocked, amber = warning, emerald = success/ok.

    @param Product $product
    @param bool $overlay  true = absolutely positioned pill on top of an image
                           (index/related cards); false = inline row (product
                           detail page, which already has its own spacing).
--}}
@props(['product', 'overlay' => true])

{{--
    has_variants: stock lives per-variant, the product's own
    stock_quantity/track_stock are not authoritative (see
    Product::getStockStatusAttribute()/totalVariantStock()) — this badge
    reads those instead of the raw columns so a variant product shown as a
    related/card item doesn't show a stale or always-zero "Out of Stock"
    from its own meaningless stock_quantity.
--}}
@php
    $trackStock = $product->has_variants ? true : $product->track_stock;
    $status     = $product->has_variants ? $product->stock_status : ($product->track_stock ? ($product->stock_quantity <= 0 ? 'out_of_stock' : ($product->stock_quantity <= 5 ? 'low' : 'ok')) : 'untracked');
    $qty        = $product->has_variants ? $product->totalVariantStock() : $product->stock_quantity;
@endphp

@if($trackStock && $status !== 'untracked')
    @if($status === 'out_of_stock')
        <span {{ $attributes->class([
            'text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-900/80 text-white' => $overlay,
            'inline-flex items-center gap-1.5 text-sm text-red-600 font-medium' => !$overlay,
        ]) }}>
            @unless($overlay)<span class="w-2 h-2 bg-red-500 rounded-full"></span>@endunless
            Out of Stock
        </span>
    @elseif($status === 'low')
        <span {{ $attributes->class([
            'text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-500 text-white' => $overlay,
            'inline-flex items-center gap-1.5 text-sm text-amber-600 font-medium' => !$overlay,
        ]) }}>
            @unless($overlay)<span class="w-2 h-2 bg-amber-500 rounded-full"></span>@endunless
            {{ $product->has_variants ? 'Low Stock' : "Only {$qty} left" }}
        </span>
    @elseif(!$overlay)
        <span class="inline-flex items-center gap-1.5 text-sm text-emerald-600 font-medium">
            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> In Stock
        </span>
    @endif
@endif
