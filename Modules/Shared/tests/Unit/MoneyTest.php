<?php

use Modules\Shared\ValueObjects\Money;

it('builds from major units without floating point errors', function () {
    expect(Money::fromMajorUnits('19.99')->cents())->toBe(1999)
        ->and(Money::fromMajorUnits(0.1)->cents())->toBe(10);
});

it('adds and multiplies precisely using integer arithmetic', function () {
    $price = Money::fromCents(1999);

    expect($price->multiply(3)->cents())->toBe(5997)
        ->and($price->add(Money::fromCents(1))->cents())->toBe(2000);
});

it('formats with a currency symbol and thousands separator', function () {
    expect(Money::fromCents(1_234_567)->format())->toBe('$12,345.67')
        ->and(Money::fromCents(0)->format())->toBe('$0.00');
});

it('compares by amount and currency', function () {
    expect(Money::fromCents(500)->equals(Money::fromCents(500)))->toBeTrue()
        ->and(Money::fromCents(500)->equals(Money::fromCents(501)))->toBeFalse();
});

it('rejects negative amounts', function () {
    Money::fromCents(-1);
})->throws(InvalidArgumentException::class);

it('refuses to combine different currencies', function () {
    Money::fromCents(100, 'USD')->add(Money::fromCents(100, 'EUR'));
})->throws(InvalidArgumentException::class);
