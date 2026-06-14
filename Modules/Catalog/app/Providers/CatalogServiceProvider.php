<?php

namespace Modules\Catalog\Providers;

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Modules\Catalog\Listeners\RecordProductSales;
use Modules\Catalog\Services\CatalogProductService;
use Modules\Shared\Contracts\ProductCatalog;
use Modules\Shared\Events\OrderPlaced;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CatalogServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Catalog';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'catalog';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        // Bind the cross-module contract to Catalog's implementation. Other
        // modules resolve ProductCatalog from the container and receive this
        // service without depending on the Catalog module at compile time.
        $this->app->bind(ProductCatalog::class, CatalogProductService::class);
    }

    public function boot(): void
    {
        parent::boot();

        // Register the module's Livewire namespace so any component under
        // Modules\Catalog\Livewire resolves as <livewire:catalog::name /> — e.g.
        // catalog::product-browser => Modules\Catalog\Livewire\ProductBrowser.
        Livewire::addNamespace(
            'catalog',
            classNamespace: 'Modules\\Catalog\\Livewire',
            viewPath: module_path('Catalog', 'resources/views/livewire'),
        );

        // React to orders placed in the Order module purely through the Shared
        // domain event — Catalog never references the Order module directly.
        Event::listen(OrderPlaced::class, RecordProductSales::class);
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
