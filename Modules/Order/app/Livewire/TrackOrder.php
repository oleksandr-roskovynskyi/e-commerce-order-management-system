<?php

declare(strict_types=1);

namespace Modules\Order\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;

/**
 * Public storefront order tracking / status display.
 *
 * Lets a customer look up one of their own orders by order number + email and
 * see its current workflow status and snapshotted line items. Like the rest of
 * the Order module's storefront, it reads only Order models — never the Catalog
 * (line items already carry product snapshots), so module boundaries hold.
 */
#[Layout('layouts.storefront')]
class TrackOrder extends Component
{
    #[Url]
    public string $orderNumber = '';

    #[Url]
    public string $email = '';

    public bool $searched = false;

    public function track(): void
    {
        $this->searched = true;
    }

    public function render(): View
    {
        return view('order::livewire.track-order', [
            'order' => $this->searched ? $this->findOrder() : null,
            'workflow' => OrderStatus::cases(),
        ]);
    }

    /**
     * Resolve the order only when both an order number and the matching email
     * are supplied, so a customer can never view someone else's order.
     */
    private function findOrder(): ?Order
    {
        $orderId = (int) $this->orderNumber;

        if ($orderId < 1 || trim($this->email) === '') {
            return null;
        }

        return Order::query()
            ->with('items')
            ->whereKey($orderId)
            ->where('customer_email', trim($this->email))
            ->first();
    }
}
