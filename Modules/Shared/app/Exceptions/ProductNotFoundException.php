<?php

declare(strict_types=1);

namespace Modules\Shared\Exceptions;

use RuntimeException;

final class ProductNotFoundException extends RuntimeException
{
    public static function withId(int $productId): self
    {
        return new self("Product #{$productId} was not found in the catalog.");
    }
}
