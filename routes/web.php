<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// The public catalog storefront is the application's landing page.
Route::redirect('/', '/catalog');
