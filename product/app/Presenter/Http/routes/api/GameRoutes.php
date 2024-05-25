<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

use App\Application\Controllers\GameController;
use App\Presenter\Http\Middleware\CartAccess;


Route::prefix('game')->group(function () {
    // Route::get('', [GameController::class, 'handle']);
    Route::get('/detail/{id}', [GameController::class, 'showGameByID']);
    Route::get('favorate', [GameController::class, 'showFavorate']);
    Route::get('search', [GameController::class, 'showSearch']);

    Route::group(['middleware' => 'jwt.verify'], function() { 
        // Các route khác cần xác thực JWT
        Route::post('', [GameController::class, 'createGame']);
        Route::delete('{id}', [GameController::class, 'deleteGame']); 
    });
    
    
});
Route::middleware(['jwt.verify', CartAccess::class])->group(function () {
        Route::prefix('cart')->group(function () {
            Route::post('add', [GameController::class, 'addToCart']);
            Route::put('update', [GameController::class, 'updateCart']);
            Route::post('buy', [GameController::class, 'payCart']);
            Route::get('/', [GameController::class, 'showCart']);
            Route::delete('remove/{productId?}', [GameController::class, 'removeFromCart']);
            Route::post('order', [GameController::class, 'orderCart']);
        });
    });