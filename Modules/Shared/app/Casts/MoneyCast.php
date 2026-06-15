<?php

declare(strict_types=1);

namespace Modules\Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\ValueObjects\Money;

/**
 * Casts an integer "minor units" column (e.g. price_cents) to a Money value
 * object on read and back to an integer on write. The currency can be passed
 * as a cast parameter, e.g. `MoneyCast::class.':USD'`.
 *
 * @implements CastsAttributes<Money, Money|int>
 */
class MoneyCast implements CastsAttributes
{
    public function __construct(
        private string $currency = 'USD',
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return Money::fromCents((int) $value, $this->currency);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->cents();
        }

        // Permit assigning a raw integer amount of minor units as well.
        return (int) $value;
    }
}
