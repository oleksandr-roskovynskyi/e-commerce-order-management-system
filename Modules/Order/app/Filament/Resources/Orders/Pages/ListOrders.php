<?php

namespace Modules\Order\Filament\Resources\Orders\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Order\Filament\Resources\Orders\OrderResource;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // No "create" header action: orders are placed through the storefront.
}
