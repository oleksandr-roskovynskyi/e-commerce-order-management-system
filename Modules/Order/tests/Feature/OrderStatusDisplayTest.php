<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Catalog\Models\Product;
use Modules\Order\Actions\CreateOrderAction;
use Modules\Order\Livewire\TrackOrder;

it('shows a customer their order status and snapshotted items', function () {
    $product = Product::factory()->create([
        'name' => 'Trackable Widget',
        'price' => 2500,
        'stock_quantity' => 10,
    ]);

    $order = app(CreateOrderAction::class)->execute('Jane Doe', 'jane@example.com', [
        ['product_id' => $product->id, 'quantity' => 2],
    ]);

    Livewire::test(TrackOrder::class)
        ->set('orderNumber', (string) $order->id)
        ->set('email', 'jane@example.com')
        ->call('track')
        ->assertSee('Order #' . $order->id)
        ->assertSee('Jane Doe')
        ->assertSee('Trackable Widget') // product snapshot on the line item
        ->assertSee('Pending');         // current workflow status
});

it('does not reveal an order to a mismatched email', function () {
    $product = Product::factory()->create([
        'name' => 'Secret Widget',
        'stock_quantity' => 5,
    ]);

    $order = app(CreateOrderAction::class)->execute('Jane Doe', 'jane@example.com', [
        ['product_id' => $product->id, 'quantity' => 1],
    ]);

    Livewire::test(TrackOrder::class)
        ->set('orderNumber', (string) $order->id)
        ->set('email', 'intruder@example.com')
        ->call('track')
        ->assertDontSee('Secret Widget')
        ->assertSee('matching that number and email');
});
