<?php

namespace Modules\Catalog\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\Shared\ValueObjects\Money;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('$')
                    ->helperText('Entered in dollars; stored internally as integer cents.')
                    ->formatStateUsing(fn (mixed $state): ?float => $state instanceof Money
                        ? $state->toMajorUnits()
                        : (is_numeric($state) ? (float) $state : null))
                    ->dehydrateStateUsing(fn (mixed $state): ?Money => ($state === null || $state === '')
                        ? null
                        : Money::fromMajorUnits((string) $state)),
                TextInput::make('stock_quantity')
                    ->label('Stock quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
