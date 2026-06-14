<?php

namespace Modules\Order\Providers;

use Livewire\Livewire;
use Nwidart\Modules\Support\ModuleServiceProvider;

class OrderServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Order';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'order';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        // Register the module's Livewire namespace so storefront components
        // resolve as <livewire:order::name /> — e.g. order::create-order maps
        // to Modules\Order\Livewire\CreateOrder.
        Livewire::addNamespace(
            'order',
            classNamespace: 'Modules\\Order\\Livewire',
            viewPath: module_path('Order', 'resources/views/livewire'),
        );
    }
}
