<?php

namespace Modules\Order\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Order\Actions\CreateOrderAction;
use Modules\Order\Enums\OrderStatus;
use Modules\Shared\Contracts\ProductCatalog;

class OrderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = app(ProductCatalog::class);
        $action = app(CreateOrderAction::class);

        $products = $catalog->availableProducts()->values();

        if ($products->count() < 2) {
            return;
        }

        $first = $products->get(0);
        $second = $products->get(1);

        // A few demo orders placed through the real flow (snapshots + stock
        // decrements via the ProductCatalog contract), left in different states.
        $confirmed = $action->execute('Alice Johnson', 'alice@example.com', [
            ['product_id' => $first->id, 'quantity' => 1],
            ['product_id' => $second->id, 'quantity' => 2],
        ]);
        $confirmed->update(['status' => OrderStatus::Confirmed]);

        $shipped = $action->execute('Bob Smith', 'bob@example.com', [
            ['product_id' => $second->id, 'quantity' => 1],
        ]);
        $shipped->update(['status' => OrderStatus::Shipped]);

        // Left as pending.
        $action->execute('Carol White', 'carol@example.com', [
            ['product_id' => $first->id, 'quantity' => 3],
        ]);
    }
}
