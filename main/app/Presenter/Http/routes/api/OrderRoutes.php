<?php

use Illuminate\Support\Facades\Route;
use App\Application\Controllers\OrderController;

// Route::middleware('auth')->group(function () {
//     Route::get('/order', [OrderController::class, 'showAllOrder']);
// });

// Route::prefix('order')->group(function () {
//     Route::get('{id}', [OrderController::class, 'showAllOrder']);
// });


Route::middleware('jwt.verify')->group(function () {
    Route::prefix('order')->group(function () {
        Route::get('', [OrderController::class, 'showAllOrder']);
    });
});