<?php

namespace Modules\Shared\DataTransferObjects;

use Modules\Shared\ValueObjects\Money;

/**
 * Neutral, read-only representation of a catalog product that is allowed to
 * cross the module boundary.
 *
 * The Order module consumes this DTO instead of the Catalog Eloquent model —
 * an anti-corruption layer that keeps the two modules fully decoupled: Order
 * never sees Catalog\Models\Product, only this stable shape.
 */
final readonly class ProductData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public Money $price,
        public int $stockQuantity,
        public ?string $category = null,
    ) {}

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stockQuantity >= $quantity;
    }
}
