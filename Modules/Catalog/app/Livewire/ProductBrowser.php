<?php

namespace Modules\Catalog\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

/**
 * Public storefront product browser.
 *
 * This is the Catalog module's own UI, so it queries Catalog models directly —
 * the cross-module ProductCatalog contract exists for *other* modules, not for
 * Catalog's own views.
 */
#[Layout('catalog::layouts.storefront')]
class ProductBrowser extends Component
{
    use WithPagination;

    #[Url]
    public ?int $categoryId = null;

    #[Url]
    public string $search = '';

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['categoryId', 'search']);
        $this->resetPage();
    }

    /**
     * @return Collection<int, Category>
     */
    public function categories(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        $products = Product::query()
            ->available()
            ->with('category')
            ->when($this->categoryId, fn (Builder $query): Builder => $query->where('category_id', $this->categoryId))
            ->when($this->search !== '', fn (Builder $query): Builder => $query->where('name', 'ilike', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(12);

        return view('catalog::livewire.product-browser', [
            'products' => $products,
            'categories' => $this->categories(),
        ]);
    }
}
