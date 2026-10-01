<?php

namespace App\Modules\Ecommerce\Exceptions;

use RuntimeException;

/**
 * Thrown when a promo code that was applied to the cart is no longer
 * usable by the time checkout is actually placed (deactivated, expired,
 * or hit max_uses in the gap between "applied to cart" and "order
 * placed"). Deliberately fails checkout rather than silently dropping the
 * discount — the customer saw a specific total on the checkout page, and
 * placing the order at a higher total without telling them would be a
 * quiet price increase, not a graceful degradation.
 */
class PromoCodeExpiredException extends RuntimeException
{
    public static function make(string $code): self
    {
        return new self("The promo code \"{$code}\" is no longer valid. It's been removed from your cart — please review your order and try again.");
    }
}
