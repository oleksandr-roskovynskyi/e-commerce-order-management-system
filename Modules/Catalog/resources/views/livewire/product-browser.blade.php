<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Products</h1>
            <p class="text-sm text-gray-500">Browse everything that's currently in stock.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search products…"
                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:w-56"
            />
            <select
                wire:model.live="categoryId"
                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:w-48"
            >
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($products->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-sm text-gray-500">
            No products match your filters.
            <button wire:click="clearFilters" class="font-medium text-indigo-600 underline">Clear filters</button>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <article
                    wire:key="product-{{ $product->id }}"
                    class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                            {{ $product->category?->name ?? 'Uncategorised' }}
                        </span>
                        <span class="text-xs font-medium text-green-600">{{ $product->stock_quantity }} in stock</span>
                    </div>

                    <h2 class="text-base font-semibold text-gray-900">{{ $product->name }}</h2>
                    <p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ $product->description }}</p>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-lg font-semibold">{{ $product->price->format() }}</span>
                        <span class="text-xs text-gray-400">{{ $product->sku }}</span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</div>
