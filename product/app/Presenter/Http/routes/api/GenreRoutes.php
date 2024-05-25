<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Route;

use App\Application\Controllers\GenreController;


Route::prefix('genre')->group(function () {
    Route::get('', [GenreController::class, 'showAllGenre']);
    Route::group(['middleware' => 'jwt.verify'], function() {
        Route::post('', [GenreController::class, 'createGenre']);
        Route::delete('{id}', [GenreController::class, 'deleteGenre']);
        // Các route khác cần xác thực JWT  
    });
});