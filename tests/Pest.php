<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Bind the application's base TestCase to the feature tests. Module feature
| tests (Modules/<Name>/tests/Feature) and the RefreshDatabase trait are
| wired up once the modules exist — see the additional bindings below.
|
*/

pest()->extend(TestCase::class)->in('Feature');
