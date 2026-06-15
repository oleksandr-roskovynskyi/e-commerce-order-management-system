<?php

declare(strict_types=1);

namespace Modules\Order\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Shared\ValueObjects\Money;

/**
 * Read-only view of an order's snapshotted line items. Items are written once,
 * when the order is placed, so there are no create/edit/delete actions here.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Line items';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product'),
                TextColumn::make('quantity'),
                TextColumn::make('unit_price')
                    ->formatStateUsing(fn (Money $state): string => $state->format()),
                TextColumn::make('line_total')
                    ->formatStateUsing(fn (Money $state): string => $state->format()),
            ]);
    }
}
