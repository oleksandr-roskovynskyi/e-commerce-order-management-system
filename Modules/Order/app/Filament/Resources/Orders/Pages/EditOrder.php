<?php

declare(strict_types=1);

namespace Modules\Order\Filament\Resources\Orders\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Order\Filament\Resources\Orders\Actions\AdvanceOrderStatusAction;
use Modules\Order\Filament\Resources\Orders\OrderResource;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            // Advance the order one step through its status workflow, the same
            // guarded action offered in the orders table.
            AdvanceOrderStatusAction::make(),
            DeleteAction::make(),
        ];
    }
}
