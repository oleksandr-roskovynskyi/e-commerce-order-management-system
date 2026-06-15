<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Module boundary architecture tests
|--------------------------------------------------------------------------
|
| These automatically enforce the core rule of the system: the Order and
| Catalog modules must never reference each other directly — they may only
| collaborate through the Shared kernel (contracts, DTOs, value objects,
| events). If someone adds a forbidden `use Modules\Catalog\...` to the Order
| module, this test fails.
|
*/

arch('the order module does not depend on the catalog module')
    ->expect('Modules\Order')
    ->not->toUse('Modules\Catalog');

arch('the catalog module does not depend on the order module')
    ->expect('Modules\Catalog')
    ->not->toUse('Modules\Order');

arch('the shared kernel depends on neither feature module')
    ->expect('Modules\Shared')
    ->not->toUse(['Modules\Catalog', 'Modules\Order']);

arch('the shared kernel defines no eloquent models of its own')
    ->expect('Modules\Shared')
    ->not->toExtend('Illuminate\Database\Eloquent\Model');

arch('shared value objects and DTOs are immutable')
    ->expect(['Modules\Shared\ValueObjects', 'Modules\Shared\DataTransferObjects'])
    ->toBeFinal()
    ->toBeReadonly();
