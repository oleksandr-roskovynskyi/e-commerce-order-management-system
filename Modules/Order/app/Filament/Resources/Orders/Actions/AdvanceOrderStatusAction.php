<?php

declare(strict_types=1);

namespace Modules\Order\Filament\Resources\Orders\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Livewire\Component;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;

/**
 * Guarded "advance to next status" action, shared by the orders table and the
 * order edit page. The workflow is strictly linear, so every non-final status
 * has exactly one valid next status, which {@see OrderStatus::canTransitionTo()}
 * enforces — the action only ever moves an order one step forward.
 */
class AdvanceOrderStatusAction
{
    public static function make(): Action
    {
        return Action::make('advance')
            ->label(fn (Order $record): string => $record->status->isFinal()
                ? 'Completed'
                : 'Advance to ' . $record->status->allowedTransitions()[0]->label())
            ->icon('heroicon-o-arrow-right-circle')
            ->color('primary')
            ->visible(fn (Order $record): bool => ! $record->status->isFinal())
            ->requiresConfirmation()
            ->action(function (Order $record, Component $livewire): void {
                $next = $record->status->allowedTransitions()[0] ?? null;

                if ($next === null || ! $record->status->canTransitionTo($next)) {
                    return;
                }

                $record->update(['status' => $next]);

                // Keep the edit page's (read-only) status field in sync after the
                // change; the table view re-renders on its own.
                if ($livewire instanceof EditRecord) {
                    $livewire->refreshFormData(['status']);
                }

                Notification::make()
                    ->title("Order #{$record->id} is now {$next->label()}")
                    ->success()
                    ->send();
            });
    }
}
