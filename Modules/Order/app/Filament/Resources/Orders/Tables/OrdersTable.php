<?php

namespace Modules\Order\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
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
                self::advanceStatusAction(),
                EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    /**
     * A single-button, guarded "advance to next status" action. Because the
     * workflow is strictly linear, each non-final status has exactly one valid
     * next status, which {@see OrderStatus::canTransitionTo()} enforces.
     */
    private static function advanceStatusAction(): Action
    {
        return Action::make('advance')
            ->label(fn (Order $record): string => $record->status->isFinal()
                ? 'Completed'
                : 'Advance to '.$record->status->allowedTransitions()[0]->label())
            ->icon('heroicon-o-arrow-right-circle')
            ->color('primary')
            ->visible(fn (Order $record): bool => ! $record->status->isFinal())
            ->requiresConfirmation()
            ->action(function (Order $record): void {
                $next = $record->status->allowedTransitions()[0];

                if (! $record->status->canTransitionTo($next)) {
                    return;
                }

                $record->update(['status' => $next]);

                Notification::make()
                    ->title("Order #{$record->id} is now {$next->label()}")
                    ->success()
                    ->send();
            });
    }
}
