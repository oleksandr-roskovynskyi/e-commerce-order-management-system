<?php

namespace Modules\Order\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\Order\Actions\CreateOrderAction;
use Modules\Shared\Contracts\ProductCatalog;
use Modules\Shared\Exceptions\InsufficientStockException;
use Modules\Shared\ValueObjects\Money;

/**
 * Storefront order creation.
 *
 * Like the rest of the Order module, this component never touches a Catalog
 * model: it lists products and reads their data through the ProductCatalog
 * contract, and persists the order through CreateOrderAction.
 */
#[Layout('layouts.storefront')]
class CreateOrder extends Component
{
    #[Validate('required|string|max:255')]
    public string $customerName = '';

    #[Validate('required|email|max:255')]
    public string $customerEmail = '';

    /**
     * Cart contents as a map of product id => quantity.
     *
     * @var array<int, int>
     */
    public array $cart = [];

    public ?int $placedOrderId = null;

    public function addToCart(int $productId): void
    {
        $product = app(ProductCatalog::class)->find($productId);

        if ($product === null) {
            return;
        }

        $current = $this->cart[$productId] ?? 0;

        // Never let the cart exceed the available stock.
        if ($current < $product->stockQuantity) {
            $this->cart[$productId] = $current + 1;
        }
    }

    public function decrement(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]--;

        if ($this->cart[$productId] < 1) {
            unset($this->cart[$productId]);
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function placeOrder(CreateOrderAction $action): void
    {
        $this->validate();

        if ($this->cart === []) {
            $this->addError('cart', 'Add at least one product to your order.');

            return;
        }

        $lines = [];
        foreach ($this->cart as $productId => $quantity) {
            $lines[] = ['product_id' => (int) $productId, 'quantity' => (int) $quantity];
        }

        try {
            $order = $action->execute($this->customerName, $this->customerEmail, $lines);
        } catch (InsufficientStockException $exception) {
            $this->addError('cart', $exception->getMessage());

            return;
        }

        $this->placedOrderId = $order->id;
        $this->reset(['cart', 'customerName', 'customerEmail']);
    }

    public function startNewOrder(): void
    {
        $this->reset();
    }

    public function render(): View
    {
        $catalog = app(ProductCatalog::class);
        $products = $catalog->availableProducts();

        $cartLines = $products
            ->filter(fn ($product): bool => isset($this->cart[$product->id]))
            ->map(fn ($product): array => [
                'product' => $product,
                'quantity' => $this->cart[$product->id],
                'lineTotal' => $product->price->multiply($this->cart[$product->id]),
            ])
            ->values();

        $total = $cartLines->reduce(
            fn (Money $carry, array $line): Money => $carry->add($line['lineTotal']),
            Money::zero(),
        );

        return view('order::livewire.create-order', [
            'products' => $products,
            'cartLines' => $cartLines,
            'total' => $total,
        ]);
    }
}
