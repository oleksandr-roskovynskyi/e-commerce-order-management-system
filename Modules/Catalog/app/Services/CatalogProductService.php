<?php

namespace Modules\Catalog\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\Shared\Contracts\ProductCatalog;
use Modules\Shared\DataTransferObjects\ProductData;
use Modules\Shared\Exceptions\InsufficientStockException;
use Modules\Shared\Exceptions\ProductNotFoundException;

/**
 * Catalog's concrete implementation of the cross-module ProductCatalog contract.
 *
 * Everything that other modules are allowed to know about products flows through
 * here, and always as a neutral {@see ProductData} DTO — never as an Eloquent
 * model. This is what lets the Order module work with products without ever
 * touching Catalog\Models\Product.
 */
class CatalogProductService implements ProductCatalog
{
    public function availableProducts(): Collection
    {
        return Product::query()
            ->available()
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): ProductData => $this->toData($product))
            ->values();
    }

    public function find(int $productId): ?ProductData
    {
        $product = Product::query()->with('category')->find($productId);

        return $product instanceof Product ? $this->toData($product) : null;
    }

    public function findMany(array $productIds): Collection
    {
        return Product::query()
            ->with('category')
            ->whereIn('id', $productIds)
            ->get()
            ->mapWithKeys(fn (Product $product): array => [$product->id => $this->toData($product)]);
    }

    public function isAvailable(int $productId, int $quantity): bool
    {
        $product = Product::query()->find($productId);

        return $product instanceof Product
            && $product->is_active
            && $product->stock_quantity >= $quantity;
    }

    public function decrementStock(int $productId, int $quantity): void
    {
        DB::transaction(function () use ($productId, $quantity): void {
            // Pessimistic lock so concurrent orders cannot oversell the product.
            $product = Product::query()->lockForUpdate()->find($productId);

            if (! $product instanceof Product) {
                throw ProductNotFoundException::withId($productId);
            }

            if ($product->stock_quantity < $quantity) {
                throw InsufficientStockException::for($productId, $quantity, $product->stock_quantity);
            }

            $product->decrement('stock_quantity', $quantity);
        });
    }

    private function toData(Product $product): ProductData
    {
        return new ProductData(
            id: $product->id,
            name: $product->name,
            description: $product->description,
            price: $product->price,
            stockQuantity: $product->stock_quantity,
            category: $product->category?->name,
        );
    }
}
