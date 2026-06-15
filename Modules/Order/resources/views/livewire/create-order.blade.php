<div>
    @if ($placedOrderId)
        <div class="mx-auto max-w-xl rounded-xl border border-green-200 bg-green-50 p-8 text-center">
            <h1 class="text-2xl font-semibold text-green-800">Thank you!</h1>
            <p class="mt-2 text-green-700">
                Your order <span class="font-semibold">#{{ $placedOrderId }}</span> has been placed.
            </p>
            <p class="mt-1 text-sm text-green-700">
                Current status:
                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">Pending</span>
            </p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a
                    href="{{ route('orders.track', ['orderNumber' => $placedOrderId, 'email' => $placedOrderEmail]) }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Track your order
                </a>
                <button
                    wire:click="startNewOrder"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
                >
                    Place another order
                </button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            {{-- Product picker --}}
            <div class="lg:col-span-2">
                <h1 class="mb-4 text-2xl font-semibold tracking-tight">New order</h1>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($products as $product)
                        <div wire:key="p-{{ $product->id }}" class="flex flex-col rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                    {{ $product->category ?? 'Uncategorised' }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $product->stockQuantity }} in stock</span>
                            </div>
                            <h2 class="mt-2 font-semibold">{{ $product->name }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ $product->description }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-lg font-semibold">{{ $product->price->format() }}</span>
                                <button
                                    wire:click="addToCart({{ $product->id }})"
                                    class="rounded-lg bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700"
                                >
                                    Add
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cart + customer details --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">Your order</h2>

                    @error('cart')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($cartLines->isEmpty())
                        <p class="mt-4 text-sm text-gray-500">Your cart is empty — add some products to get started.</p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-100">
                            @foreach ($cartLines as $line)
                                <li wire:key="c-{{ $line['product']->id }}" class="flex items-center gap-2 py-3">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">{{ $line['product']->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $line['product']->price->format() }} each</p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <button wire:click="decrement({{ $line['product']->id }})" class="h-6 w-6 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">-</button>
                                        <span class="w-6 text-center text-sm">{{ $line['quantity'] }}</span>
                                        <button wire:click="addToCart({{ $line['product']->id }})" class="h-6 w-6 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">+</button>
                                    </div>
                                    <span class="w-20 text-right text-sm font-semibold">{{ $line['lineTotal']->format() }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4">
                            <span class="text-sm font-medium text-gray-600">Total</span>
                            <span class="text-lg font-semibold">{{ $total->format() }}</span>
                        </div>
                    @endif

                    <div class="mt-6 space-y-3">
                        <div>
                            <input type="text" wire:model="customerName" placeholder="Full name"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                            @error('customerName')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <input type="email" wire:model="customerEmail" placeholder="Email address"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                            @error('customerEmail')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button
                            wire:click="placeOrder"
                            wire:loading.attr="disabled"
                            @disabled($cartLines->isEmpty())
                            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Place order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
