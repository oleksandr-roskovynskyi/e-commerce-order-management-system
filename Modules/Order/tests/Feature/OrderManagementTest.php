<?php

use App\Models\User;
use Livewire\Livewire;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Filament\Resources\Orders\Pages\ListOrders;
use Modules\Order\Models\Order;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists orders in the admin table', function () {
    $orders = Order::factory()->count(3)->create();

    Livewire::test(ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

it('advances an order to the next status in the workflow', function () {
    $order = Order::factory()->status(OrderStatus::Pending)->create();

    Livewire::test(ListOrders::class)
        ->callTableAction('advance', $order);

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

it('hides the advance action once an order is delivered', function () {
    $order = Order::factory()->status(OrderStatus::Delivered)->create();

    Livewire::test(ListOrders::class)
        ->assertTableActionHidden('advance', $order);
});

it('can filter orders by status', function () {
    Order::factory()->status(OrderStatus::Pending)->create(['customer_name' => 'PendingPerson']);
    Order::factory()->status(OrderStatus::Delivered)->create(['customer_name' => 'DeliveredPerson']);

    Livewire::test(ListOrders::class)
        ->filterTable('status', OrderStatus::Pending->value)
        ->assertCanSeeTableRecords(Order::query()->where('status', OrderStatus::Pending)->get())
        ->assertCanNotSeeTableRecords(Order::query()->where('status', OrderStatus::Delivered)->get());
});
