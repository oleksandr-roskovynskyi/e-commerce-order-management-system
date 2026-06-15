<?php

declare(strict_types=1);

namespace Modules\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Raised once an order has been persisted.
 *
 * It lives in the Shared kernel so any module can listen to it without coupling
 * to the Order module's internals, and it carries only primitive identifiers
 * (not Eloquent models) to keep module boundaries intact. This is the optional,
 * event-driven communication path; stock is decremented synchronously inside
 * the order transaction so this event never mutates stock itself.
 */
final class OrderPlaced
{
    use Dispatchable;

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     */
    public function __construct(
        public readonly int $orderId,
        public readonly array $lines,
    ) {}
}
