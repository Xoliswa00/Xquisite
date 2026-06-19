<?php

namespace App\Modules\Ecommerce\Exceptions;

use RuntimeException;

/**
 * Thrown when an online order cannot be fulfilled because a tracked
 * product no longer has enough stock at the moment of checkout.
 */
class InsufficientStockException extends RuntimeException
{
    public static function for(string $productName, int $available, int $requested): self
    {
        $available = max(0, $available);

        $message = $available === 0
            ? "\"{$productName}\" is now out of stock."
            : "Only {$available} of \"{$productName}\" left in stock (you requested {$requested}).";

        return new self($message);
    }
}
