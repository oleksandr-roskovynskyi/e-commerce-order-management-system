<?php

use Modules\Catalog\Models\Product;
use Modules\Catalog\Services\CatalogProductService;
use Modules\Order\Actions\CreateOrderAction;
use Modules\Order\Models\Order;
use Modules\Shared\Contracts\ProductCatalog;
use Modules\Shared\Exceptions\InsufficientStockException;

it('resolves the ProductCatalog contract to the catalog implementation', function () {
    expect(app(ProductCatalog::class))->toBeInstanceOf(CatalogProductService::class);
});

it('creates an order purely through the catalog contract', function () {
    $product = Product::factory()->create(['price' => 2500, 'stock_quantity' => 10]);

    $order = app(CreateOrderAction::class)->execute('Sam Carter', 'sam@example.com', [
        ['product_id' => $product->id, 'quantity' => 3],
    ]);

    expect($order->total->cents())->toBe(7500)
        ->and($order->items->first()->product_name)->toBe($product->name)
        ->and($product->fresh()->stock_quantity)->toBe(7);
});

it('rolls back the entire order when any line has insufficient stock', function () {
    $plenty = Product::factory()->create(['stock_quantity' => 10]);
    $scarce = Product::factory()->create(['stock_quantity' => 1]);

    $place = fn () => app(CreateOrderAction::class)->execute('Sam Carter', 'sam@example.com', [
        ['product_id' => $plenty->id, 'quantity' => 1],
        ['product_id' => $scarce->id, 'quantity' => 5], // exceeds stock
    ]);

    expect($place)->toThrow(InsufficientStockException::class);

    // The transaction rolled back: no order persisted and stock is untouched.
    expect(Order::query()->count())->toBe(0)
        ->and($plenty->fresh()->stock_quantity)->toBe(10)
        ->and($scarce->fresh()->stock_quantity)->toBe(1);
});
