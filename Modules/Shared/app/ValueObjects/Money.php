<?php

namespace Modules\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable money value object.
 *
 * The amount is stored as an integer number of minor units (e.g. cents) plus an
 * ISO-4217 currency code. Keeping money in integer minor units means arithmetic
 * never touches floats, so there are no rounding/precision errors — a deliberate
 * choice over decimal/float columns.
 */
final readonly class Money
{
    public function __construct(
        public int $cents,
        public string $currency = 'USD',
    ) {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents, $currency);
    }

    /**
     * Build from a major-unit amount (e.g. dollars) such as "19.99" or 19.99.
     * The amount is rounded to the nearest minor unit before being stored.
     */
    public static function fromMajorUnits(int|float|string $amount, string $currency = 'USD'): self
    {
        return new self((int) round((float) $amount * 100), $currency);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * The amount expressed in major units (e.g. dollars) for display/input.
     */
    public function toMajorUnits(): float
    {
        return $this->cents / 100;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->cents + $other->cents, $this->currency);
    }

    public function multiply(int $quantity): self
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative.');
        }

        return new self($this->cents * $quantity, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents && $this->currency === $other->currency;
    }

    /**
     * Zero amount, useful as the identity element when summing line totals.
     */
    public static function zero(string $currency = 'USD'): self
    {
        return new self(0, $currency);
    }

    public function format(): string
    {
        $major = intdiv($this->cents, 100);
        $minor = str_pad((string) ($this->cents % 100), 2, '0', STR_PAD_LEFT);
        $amount = number_format($major).'.'.$minor;

        return match ($this->currency) {
            'USD' => '$'.$amount,
            'EUR' => '€'.$amount,
            'GBP' => '£'.$amount,
            default => $amount.' '.$this->currency,
        };
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot combine amounts in different currencies: {$this->currency} and {$other->currency}."
            );
        }
    }
}
