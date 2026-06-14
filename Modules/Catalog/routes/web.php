<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Livewire\ProductBrowser;

/*
|--------------------------------------------------------------------------
| Catalog web routes
|--------------------------------------------------------------------------
|
| Public storefront browsing, rendered by a full-page Livewire component.
|
*/

Route::get('catalog', ProductBrowser::class)->name('catalog.browse');
