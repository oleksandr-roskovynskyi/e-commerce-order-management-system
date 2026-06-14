<?php

use Modules\Order\Enums\OrderStatus;

it('allows only forward, single-step transitions', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Confirmed))->toBeTrue()
        ->and(OrderStatus::Confirmed->canTransitionTo(OrderStatus::Shipped))->toBeTrue()
        ->and(OrderStatus::Shipped->canTransitionTo(OrderStatus::Delivered))->toBeTrue();
});

it('forbids skipping or reversing the workflow', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Shipped))->toBeFalse()
        ->and(OrderStatus::Shipped->canTransitionTo(OrderStatus::Pending))->toBeFalse()
        ->and(OrderStatus::Confirmed->canTransitionTo(OrderStatus::Confirmed))->toBeFalse();
});

it('marks delivered as the final state', function () {
    expect(OrderStatus::Delivered->isFinal())->toBeTrue()
        ->and(OrderStatus::Delivered->allowedTransitions())->toBe([])
        ->and(OrderStatus::Pending->isFinal())->toBeFalse();
});
