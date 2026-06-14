<?php

namespace Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * The Shared module is a pure domain "kernel": it ships only cross-module
 * contracts, value objects, DTOs, casts and domain events. It deliberately
 * has no HTTP layer, database tables, views or routes, so this provider stays
 * intentionally empty — the abstractions it exposes are bound by the modules
 * that implement them (e.g. Catalog binds the ProductCatalog contract).
 */
class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
