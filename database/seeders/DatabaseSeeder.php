<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Catalog\Database\Seeders\CatalogDatabaseSeeder;
use Modules\Order\Database\Seeders\OrderDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Model events are intentionally left enabled so seeding runs through the
     * same domain logic as the app (e.g. Category auto-generates its slug).
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $this->call([
            CatalogDatabaseSeeder::class,
            OrderDatabaseSeeder::class,
        ]);
    }
}
