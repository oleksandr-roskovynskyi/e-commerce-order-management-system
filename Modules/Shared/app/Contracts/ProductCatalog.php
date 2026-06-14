<?php

namespace Modules\Shared\Contracts;

use Illuminate\Support\Collection;
use Modules\Shared\DataTransferObjects\ProductData;
use Modules\Shared\Exceptions\InsufficientStockException;
use Modules\Shared\Exceptions\ProductNotFoundException;

/**
 * Public API of the product catalog as seen by *other* modules.
 *
 * This interface is the single seam through which the Order module talks to the
 * Catalog module. Consumers depend only on this contract (resolved from the
 * service container); the Catalog module binds its concrete implementation.
 * Neither module references the other's models or services directly, which is
 * the core decoupling requirement of this system.
 */
interface ProductCatalog
{
    /**
     * Products that are active and currently available to order.
     *
     * @return Collection<int, ProductData>
     */
    public function availableProducts(): Collection;

    /**
     * Find a single product by id, or null when it does not exist.
     */
    public function find(int $productId): ?ProductData;

    /**
     * Find several products by id, keyed by product id.
     *
     * @param  array<int, int>  $productIds
     * @return Collection<int, ProductData>
     */
    public function findMany(array $productIds): Collection;

    /**
     * Whether the requested quantity of a product can currently be ordered.
     */
    public function isAvailable(int $productId, int $quantity): bool;

    /**
     * Atomically decrement a product's stock by the given quantity.
     *
     * @throws ProductNotFoundException     when the product does not exist
     * @throws InsufficientStockException   when not enough stock is available
     */
    public function decrementStock(int $productId, int $quantity): void;
}
