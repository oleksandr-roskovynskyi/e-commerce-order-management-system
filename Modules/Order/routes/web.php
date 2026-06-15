<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Order\Livewire\CreateOrder;
use Modules\Order\Livewire\TrackOrder;

/*
|--------------------------------------------------------------------------
| Order web routes
|--------------------------------------------------------------------------
|
| Public storefront order creation and status tracking, each rendered by a
| full-page Livewire component.
|
*/

Route::get('orders/create', CreateOrder::class)->name('orders.create');
Route::get('orders/track', TrackOrder::class)->name('orders.track');
