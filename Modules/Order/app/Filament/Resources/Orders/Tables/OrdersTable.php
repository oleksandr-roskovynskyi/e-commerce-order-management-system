<?php

declare(strict_types=1);

namespace Modules\Order\Filament\Resources\Orders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Filament\Resources\Orders\Actions\AdvanceOrderStatusAction;
use Modules\Shared\ValueObjects\Money;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order')
                    ->prefix('#')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->color()),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge(),
                TextColumn::make('total')
                    ->formatStateUsing(fn (Money $state): string => $state->format())
                    ->sortable(),
                TextColumn::make('placed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(fn (): array => collect(OrderStatus::cases())
                        ->mapWithKeys(fn (OrderStatus $status): array => [$status->value => $status->label()])
                        ->all()),
            ])
            ->recordActions([
                AdvanceOrderStatusAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }
}
