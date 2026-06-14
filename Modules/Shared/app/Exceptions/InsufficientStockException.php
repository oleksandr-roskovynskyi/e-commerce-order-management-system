<?php

namespace Modules\Shared\Exceptions;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public static function for(int $productId, int $requested, int $available): self
    {
        return new self(
            "Insufficient stock for product #{$productId}: requested {$requested}, only {$available} available."
        );
    }
}
