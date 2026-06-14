<?php

namespace Modules\Catalog\Filament\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Catalog\Filament\Resources\Products\ProductResource;
use Modules\Shared\ValueObjects\Money;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @return array<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Display the stored cents as a dollar amount in the form.
        $price = $data['price'] ?? null;
        $data['price'] = $price instanceof Money
            ? $price->toMajorUnits()
            : (is_numeric($price) ? (int) $price / 100 : $price);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['price'] = Money::fromMajorUnits((string) $data['price'])->cents();

        return $data;
    }
}
