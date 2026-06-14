<?php

namespace Modules\Order\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use Modules\Shared\Contracts\ProductCatalog;
use Modules\Shared\Events\OrderPlaced;
use Modules\Shared\Exceptions\ProductNotFoundException;
use Modules\Shared\ValueObjects\Money;

/**
 * Creates an order from a set of requested product lines.
 *
 * This is the Order module's only point of contact with product data, and it
 * speaks exclusively through the {@see ProductCatalog} contract — it never
 * touches a Catalog model or service. Everything runs inside a single database
 * transaction so stock decrements and the persisted order are all-or-nothing.
 */
class CreateOrderAction
{
    public function __construct(
        private readonly ProductCatalog $catalog,
    ) {}

    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $lines
     *
     * @throws ProductNotFoundException
     * @throws \Modules\Shared\Exceptions\InsufficientStockException
     * @throws InvalidArgumentException  when no valid line items are supplied
     */
    public function execute(string $customerName, string $customerEmail, array $lines): Order
    {
        return DB::transaction(function () use ($customerName, $customerEmail, $lines): Order {
            $order = Order::create([
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'status' => OrderStatus::Pending,
                'total' => Money::zero(),
                'placed_at' => now(),
            ]);

            $total = Money::zero();

            /** @var list<array{product_id: int, quantity: int}> $eventLines */
            $eventLines = [];

            foreach ($lines as $line) {
                $productId = (int) $line['product_id'];
                $quantity = (int) $line['quantity'];

                if ($quantity < 1) {
                    continue;
                }

                $product = $this->catalog->find($productId);

                if ($product === null) {
                    throw ProductNotFoundException::withId($productId);
                }

                // Reserve stock through the contract; throws on insufficient stock.
                $this->catalog->decrementStock($productId, $quantity);

                $lineTotal = $product->price->multiply($quantity);
                $total = $total->add($lineTotal);

                // Snapshot the product onto the line so the order is immutable
                // against later catalog changes.
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                $eventLines[] = ['product_id' => $productId, 'quantity' => $quantity];
            }

            if ($eventLines === []) {
                throw new InvalidArgumentException('An order must contain at least one line item.');
            }

            $order->update(['total' => $total]);

            OrderPlaced::dispatch($order->id, $eventLines);

            return $order->load('items');
        });
    }
}
