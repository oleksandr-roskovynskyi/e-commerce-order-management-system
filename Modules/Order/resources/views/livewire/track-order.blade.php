<div>
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-semibold tracking-tight">Track your order</h1>
        <p class="mt-1 text-sm text-gray-500">Enter your order number and the email you used at checkout.</p>

        <form wire:submit="track" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600">Order number</label>
                <input
                    type="text"
                    wire:model="orderNumber"
                    placeholder="e.g. 12"
                    class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                />
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600">Email</label>
                <input
                    type="email"
                    wire:model="email"
                    placeholder="you@example.com"
                    class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                />
            </div>
            <button
                type="submit"
                class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
            >
                Track
            </button>
        </form>

        @if ($searched)
            @if ($order)
                @php
                    $currentIndex = array_search($order->status, $workflow, true);
                    $badge = match ($order->status->color()) {
                        'warning' => 'bg-amber-100 text-amber-800',
                        'info' => 'bg-sky-100 text-sky-800',
                        'primary' => 'bg-indigo-100 text-indigo-800',
                        'success' => 'bg-green-100 text-green-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp

                <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Order #{{ $order->id }}</h2>
                            <p class="text-sm text-gray-500">{{ $order->customer_name }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                            {{ $order->status->label() }}
                        </span>
                    </div>

                    {{-- Workflow timeline --}}
                    <ol class="mt-6 flex items-center gap-2">
                        @foreach ($workflow as $index => $step)
                            <li class="flex flex-1 flex-col items-center text-center">
                                <span @class([
                                    'flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold',
                                    'bg-gray-900 text-white' => $index <= $currentIndex,
                                    'bg-gray-100 text-gray-400' => $index > $currentIndex,
                                ])>{{ $index + 1 }}</span>
                                <span @class([
                                    'mt-1 text-xs',
                                    'font-medium text-gray-900' => $index <= $currentIndex,
                                    'text-gray-400' => $index > $currentIndex,
                                ])>{{ $step->label() }}</span>
                            </li>
                        @endforeach
                    </ol>

                    {{-- Line items (product snapshots) --}}
                    <ul class="mt-6 divide-y divide-gray-100 border-t border-gray-200">
                        @foreach ($order->items as $item)
                            <li wire:key="i-{{ $item->id }}" class="flex items-center justify-between py-3 text-sm">
                                <span class="font-medium">{{ $item->product_name }}</span>
                                <span class="text-gray-500">{{ $item->quantity }} × {{ $item->unit_price->format() }}</span>
                                <span class="font-semibold">{{ $item->line_total->format() }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4">
                        <span class="text-sm font-medium text-gray-600">Total</span>
                        <span class="text-lg font-semibold">{{ $order->total->format() }}</span>
                    </div>
                </div>
            @else
                <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                    We couldn't find an order matching that number and email.
                </div>
            @endif
        @endif
    </div>
</div>
