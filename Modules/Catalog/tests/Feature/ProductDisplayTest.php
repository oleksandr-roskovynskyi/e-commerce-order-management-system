<?php

use Livewire\Livewire;
use Modules\Catalog\Livewire\ProductBrowser;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

it('shows only active, in-stock products', function () {
    Product::factory()->create(['name' => 'VisibleWidget', 'stock_quantity' => 5, 'is_active' => true]);
    Product::factory()->outOfStock()->create(['name' => 'OutOfStockWidget']);
    Product::factory()->inactive()->create(['name' => 'InactiveWidget']);

    Livewire::test(ProductBrowser::class)
        ->assertSee('VisibleWidget')
        ->assertDontSee('OutOfStockWidget')
        ->assertDontSee('InactiveWidget');
});

it('filters products by category', function () {
    $alpha = Category::factory()->create(['name' => 'Alpha']);
    $beta = Category::factory()->create(['name' => 'Beta']);
    Product::factory()->create(['category_id' => $alpha->id, 'name' => 'AlphaWidget', 'stock_quantity' => 5]);
    Product::factory()->create(['category_id' => $beta->id, 'name' => 'BetaWidget', 'stock_quantity' => 5]);

    Livewire::test(ProductBrowser::class)
        ->set('categoryId', $alpha->id)
        ->assertSee('AlphaWidget')
        ->assertDontSee('BetaWidget');
});

it('searches products by name', function () {
    Product::factory()->create(['name' => 'Findable Gadget', 'stock_quantity' => 5]);
    Product::factory()->create(['name' => 'Unrelated Thing', 'stock_quantity' => 5]);

    Livewire::test(ProductBrowser::class)
        ->set('search', 'Findable')
        ->assertSee('Findable Gadget')
        ->assertDontSee('Unrelated Thing');
});
