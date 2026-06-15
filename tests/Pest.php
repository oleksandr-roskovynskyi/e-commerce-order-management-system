<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case & database
|--------------------------------------------------------------------------
|
| Feature tests — both the application's own and each module's
| (Modules/<Name>/tests/Feature) — boot the full framework and run against a
| fresh database. Module unit tests (e.g. the Money value object) stay as plain,
| framework-free Pest tests for speed.
|
*/

uses(TestCase::class, RefreshDatabase::class)->in(
    'Feature',
    __DIR__ . '/../Modules/Catalog/tests/Feature',
    __DIR__ . '/../Modules/Order/tests/Feature',
);
