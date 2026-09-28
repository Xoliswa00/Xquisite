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

@if($product->track_stock)
    @if($product->stock_quantity <= 0)
        <span {{ $attributes->class([
            'text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-900/80 text-white' => $overlay,
            'inline-flex items-center gap-1.5 text-sm text-red-600 font-medium' => !$overlay,
        ]) }}>
            @unless($overlay)<span class="w-2 h-2 bg-red-500 rounded-full"></span>@endunless
            Out of Stock
        </span>
    @elseif($product->stock_quantity <= 5)
        <span {{ $attributes->class([
            'text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-500 text-white' => $overlay,
            'inline-flex items-center gap-1.5 text-sm text-amber-600 font-medium' => !$overlay,
        ]) }}>
            @unless($overlay)<span class="w-2 h-2 bg-amber-500 rounded-full"></span>@endunless
            Only {{ $product->stock_quantity }} left
        </span>
    @elseif(!$overlay)
        <span class="inline-flex items-center gap-1.5 text-sm text-emerald-600 font-medium">
            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> In Stock
        </span>
    @endif
@endif
