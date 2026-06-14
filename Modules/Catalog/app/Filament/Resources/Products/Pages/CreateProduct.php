<?php

namespace Modules\Catalog\Filament\Resources\Products\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Catalog\Filament\Resources\Products\ProductResource;
use Modules\Shared\ValueObjects\Money;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The form captures the price in dollars; persist it as integer cents.
        $data['price'] = Money::fromMajorUnits((string) $data['price'])->cents();

        return $data;
    }
}
