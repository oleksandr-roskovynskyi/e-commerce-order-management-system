<?php

namespace Modules\Order\Enums;

/**
 * Order lifecycle as an explicit, linear state machine:
 *
 *   pending → confirmed → shipped → delivered
 *
 * Transitions are validated through {@see canTransitionTo()} so an order can
 * never skip or move backwards through the workflow.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    /**
     * Statuses this status may legally move into.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed],
            self::Confirmed => [self::Shipped],
            self::Shipped => [self::Delivered],
            self::Delivered => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), strict: true);
    }

    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Filament/Tailwind colour key used for status badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Shipped => 'primary',
            self::Delivered => 'success',
        };
    }
}
