<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use Modules\Catalog\Filament\Resources\Products\Pages\CreateProduct;
use Modules\Catalog\Filament\Resources\Products\Pages\EditProduct;
use Modules\Catalog\Filament\Resources\Products\Pages\ListProducts;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists products in the admin table', function () {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

it('creates a product, storing the price as integer cents', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'category_id' => $category->id,
            'name' => 'Test Widget',
            'sku' => 'SKU-TEST-1',
            'description' => 'A nicely engineered test widget.',
            'price' => 24.50,
            'stock_quantity' => 10,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::query()->where('sku', 'SKU-TEST-1')->first();

    expect($product)->not->toBeNull()
        ->and($product->price->cents())->toBe(2450)
        ->and($product->stock_quantity)->toBe(10);
});

it('edits an existing product', function () {
    $product = Product::factory()->create(['name' => 'Original']);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['name' => 'Renamed Widget'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->name)->toBe('Renamed Widget');
});

it('deletes a product from the table', function () {
    $product = Product::factory()->create();

    Livewire::test(ListProducts::class)
        ->callTableAction('delete', $product);

    $this->assertModelMissing($product);
});
