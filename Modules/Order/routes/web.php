<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Livewire\CreateOrder;

/*
|--------------------------------------------------------------------------
| Order web routes
|--------------------------------------------------------------------------
|
| Public storefront order creation, rendered by a full-page Livewire component.
|
*/

Route::get('/orders/create', CreateOrder::class)->name('orders.create');
