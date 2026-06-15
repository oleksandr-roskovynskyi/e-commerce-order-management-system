<?php

declare(strict_types=1);

namespace Modules\Order\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event discovery only ever scans the main app's Listeners directory, so it
     * is disabled here; this module registers no listeners of its own.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];
}
