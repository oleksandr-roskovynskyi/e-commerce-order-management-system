<?php

declare(strict_types=1);

namespace Modules\Catalog\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Shared\Events\OrderPlaced;

/**
 * The event-driven side of cross-module communication.
 *
 * Catalog reacts to an order being placed without any reference to the Order
 * module — it only depends on the Shared OrderPlaced event. Stock is already
 * decremented synchronously inside the order transaction, so this listener is
 * free to do non-critical work; here it records a sales log line, but in a real
 * system it might feed analytics or an inventory projection (and could be queued).
 */
class RecordProductSales
{
    public function handle(OrderPlaced $event): void
    {
        foreach ($event->lines as $line) {
            Log::info('Catalog recorded a product sale', [
                'order_id' => $event->orderId,
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
            ]);
        }
    }
}
