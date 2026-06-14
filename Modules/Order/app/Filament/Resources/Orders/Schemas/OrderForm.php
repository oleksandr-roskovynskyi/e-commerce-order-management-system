<?php

namespace Modules\Order\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Order\Enums\OrderStatus;
use Modules\Shared\ValueObjects\Money;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('customer_email')
                    ->label('Customer email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                // Status, total and placed_at are shown for context but are not
                // editable here: status changes go through the guarded transition
                // actions, and the total is derived from the order's line items.
                Select::make('status')
                    ->options(fn (): array => collect(OrderStatus::cases())
                        ->mapWithKeys(fn (OrderStatus $status): array => [$status->value => $status->label()])
                        ->all())
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('total')
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?Money $state): ?float => $state?->toMajorUnits()),
                DateTimePicker::make('placed_at')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
