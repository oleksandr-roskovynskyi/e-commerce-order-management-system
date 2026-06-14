<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

class CatalogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // [name, price in cents, stock quantity, is_active]
        $catalog = [
            'Electronics' => [
                ['Wireless Headphones', 12999, 45, true],
                ['Mechanical Keyboard', 8999, 30, true],
                ['USB-C Hub', 3499, 0, true],            // out of stock
            ],
            'Books' => [
                ['Clean Architecture', 3299, 120, true],
                ['Refactoring, 2nd Edition', 4599, 60, true],
            ],
            'Home & Kitchen' => [
                ['Pour-Over Coffee Maker', 2499, 80, true],
                ["Chef's Knife", 6999, 25, true],
            ],
            'Apparel' => [
                ['Merino Wool Socks', 1899, 200, true],
                ['Classic Hoodie', 5499, 40, false],     // inactive (hidden from storefront)
            ],
        ];

        $sku = 1000;

        foreach ($catalog as $categoryName => $products) {
            $category = Category::query()->create(['name' => $categoryName]);

            foreach ($products as [$name, $price, $stock, $active]) {
                Product::query()->create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'sku' => 'SKU-'.(++$sku),
                    'description' => $name.' — a quality '.strtolower($categoryName).' product.',
                    'price' => $price,
                    'stock_quantity' => $stock,
                    'is_active' => $active,
                ]);
            }
        }
    }
}
