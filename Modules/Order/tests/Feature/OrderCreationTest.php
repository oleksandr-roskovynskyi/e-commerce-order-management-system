<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Catalog\Models\Product;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Livewire\CreateOrder;
use Modules\Order\Models\Order;

it('places an order, snapshots the product and decrements catalog stock', function () {
    $product = Product::factory()->create([
        'name' => 'Gizmo',
        'price' => 5000,
        'stock_quantity' => 10,
    ]);

    Livewire::test(CreateOrder::class)
        ->call('addToCart', $product->id)
        ->call('addToCart', $product->id)
        ->set('customerName', 'Jane Doe')
        ->set('customerEmail', 'jane@example.com')
        ->call('placeOrder')
        ->assertHasNoErrors();

    $order = Order::query()->with('items')->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->customer_name)->toBe('Jane Doe')
        ->and($order->total->cents())->toBe(10000)
        ->and($order->items)->toHaveCount(1);

    $item = $order->items->first();

    expect($item->product_id)->toBe($product->id)
        ->and($item->product_name)->toBe('Gizmo')
        ->and($item->unit_price->cents())->toBe(5000)
        ->and($item->quantity)->toBe(2)
        ->and($item->line_total->cents())->toBe(10000);

    expect($product->fresh()->stock_quantity)->toBe(8);
});

it('requires valid customer details', function () {
    $product = Product::factory()->create(['stock_quantity' => 5]);

    Livewire::test(CreateOrder::class)
        ->call('addToCart', $product->id)
        ->call('placeOrder')
        ->assertHasErrors(['customerName', 'customerEmail']);

    expect(Order::query()->count())->toBe(0);
});

it('rejects an empty cart', function () {
    Livewire::test(CreateOrder::class)
        ->set('customerName', 'Jane Doe')
        ->set('customerEmail', 'jane@example.com')
        ->call('placeOrder')
        ->assertHasErrors('cart');

    expect(Order::query()->count())->toBe(0);
});

it('never lets the cart exceed available stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 2]);

    Livewire::test(CreateOrder::class)
        ->call('addToCart', $product->id)
        ->call('addToCart', $product->id)
        ->call('addToCart', $product->id) // ignored: only 2 in stock
        ->set('customerName', 'Jane Doe')
        ->set('customerEmail', 'jane@example.com')
        ->call('placeOrder')
        ->assertHasNoErrors();

    $order = Order::query()->with('items')->first();

    expect($order->items->first()->quantity)->toBe(2)
        ->and($product->fresh()->stock_quantity)->toBe(0);
});
