<?php

declare(strict_types=1);

namespace Modules\Catalog\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Catalog\Listeners\RecordProductSales;
use Modules\Shared\Events\OrderPlaced;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event discovery is disabled (it only ever scans the main app's
     * Listeners directory, never a module's), so cross-module event wiring is
     * declared explicitly here instead.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * Catalog reacts to orders placed by the Order module purely through the
     * Shared OrderPlaced event — no direct reference to the Order module.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            RecordProductSales::class,
        ],
    ];
}
